<?php

namespace Modules\Payment\Http\Controllers;

use App\Http\Controllers\Controller;
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

    public function show(string $receiptNo): JsonResponse
    {
        $receipt = Receipt::with([
            'enrolment.learner',
            'enrolment.courseClass.course.subject',
            'paymentIntent',
        ])->where('receipt_no', $receiptNo)->firstOrFail();

        return response()->json(['data' => $receipt]);
    }

    public function download(Request $request, string $receiptNo): BinaryFileResponse
    {
        $receipt = Receipt::where('receipt_no', $receiptNo)->firstOrFail();

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
