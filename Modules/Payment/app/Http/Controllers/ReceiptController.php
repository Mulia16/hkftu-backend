<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Support\Ownership;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Payment\Models\Receipt;
use Modules\Payment\Services\ReceiptPdfService;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ReceiptController extends Controller
{
    public function __construct(
        private ReceiptPdfService $pdfService,
    ) {}

    public function show(Request $request, string $receiptNo): JsonResponse
    {
        $receipt = Receipt::with([
            'enrolment.learner',
            'enrolment.courseClass.course.subject',
            'paymentIntent',
        ])->where('receipt_no', $receiptNo)->firstOrFail();

        if (! Ownership::canAccessLearner($request->user(), $receipt->enrolment?->learner_id)) {
            return Ownership::forbidden();
        }

        return response()->json(['data' => $receipt]);
    }

    public function download(Request $request, string $receiptNo): BinaryFileResponse
    {
        $receipt = Receipt::with('enrolment')->where('receipt_no', $receiptNo)->firstOrFail();

        if (! Ownership::canAccessLearner($request->user(), $receipt->enrolment?->learner_id)) {
            abort(403, 'You do not have access to this resource.');
        }

        $path = $this->pdfService->getPath($receipt);

        if (!$path) {
            $this->pdfService->generate($receipt);
            $path = $this->pdfService->getPath($receipt);
        }

        if (!$path) {
            abort(404, 'Receipt PDF not found.');
        }

        return response()->file($path, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $receipt->receipt_no . '.pdf"',
        ]);
    }

    public function myReceipts(Request $request): JsonResponse
    {
        $user = $request->user();

        $receipts = Receipt::with(['enrolment.courseClass.course.subject'])
            ->whereHas('enrolment', fn ($q) => $q->where('learner_id', $user->learnerProfile?->id))
            ->orderByDesc('issued_at')
            ->paginate($request->integer('per_page', 15));

        return response()->json($receipts);
    }
}
