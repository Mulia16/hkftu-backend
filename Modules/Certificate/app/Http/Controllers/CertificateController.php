<?php

namespace Modules\Certificate\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Auth\Services\AuditLogger;
use Modules\Certificate\Models\Certificate;
use Modules\Certificate\Models\CertificateTemplate;
use Modules\Certificate\Services\CertificateEligibilityService;
use Modules\Certificate\Services\CertificatePdfService;
use Modules\Certificate\Services\CertificateService;

class CertificateController extends Controller
{
    public function __construct(
        private CertificateService $certificateService,
        private CertificateEligibilityService $eligibilityService,
        private CertificatePdfService $pdfService,
        private AuditLogger $auditLogger,
    ) {}

    public function eligibility(int $classId): JsonResponse
    {
        $results = $this->eligibilityService->calculateEligibility($classId);

        return response()->json(['data' => $results]);
    }

    public function issue(Request $request): JsonResponse
    {
        $request->validate([
            'class_id' => ['required', 'integer'],
            'template_id' => ['required', 'integer'],
            'override' => ['sometimes', 'boolean'],
            'override_reason' => ['nullable', 'string', 'max:500'],
        ]);

        $result = $this->certificateService->issueBatch(
            $request->integer('class_id'),
            $request->integer('template_id'),
            $request->user()->id,
            $request->boolean('override', false),
            $request->input('override_reason'),
        );

        $this->auditLogger->record('certificate.issue_batch', 'certificate', null, after: $result);

        return response()->json(['data' => $result], 201);
    }

    public function show(int $id): JsonResponse
    {
        $cert = Certificate::with(['enrolment.learner', 'enrolment.courseClass.course.subject', 'template', 'issuer', 'reprinter'])->findOrFail($id);

        return response()->json(['data' => $cert]);
    }

    public function index(Request $request): JsonResponse
    {
        $certificates = $this->certificateService->listAll($request->input('status'));

        return response()->json($certificates);
    }

    public function reprint(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => ['nullable', 'string', 'max:500']]);

        $cert = $this->certificateService->reprint($id, $request->user()->id, $request->input('reason'));

        $this->auditLogger->record('certificate.reprint', 'certificate', $cert->id, after: $cert->toArray());

        return response()->json(['data' => $cert]);
    }

    public function templates(): JsonResponse
    {
        $templates = CertificateTemplate::where('status', 'active')->orderBy('name')->get();

        return response()->json(['data' => $templates]);
    }

    public function pdf(int $id): JsonResponse
    {
        $cert = Certificate::findOrFail($id);

        if (! $cert->pdf_file_path) {
            $this->pdfService->generate($cert);
            $cert->refresh();
        }

        $path = $this->pdfService->getPath($cert);

        if (! $path) {
            return ApiError::respond('PDF_NOT_FOUND', 'PDF file not found on disk.', 404);
        }

        return response()->json(['data' => ['pdf_path' => $cert->pdf_file_path, 'download_url' => '/storage/'.$cert->pdf_file_path]]);
    }

    public function myCertificates(Request $request): JsonResponse
    {
        $learner = $request->user()->learnerProfile;
        if (! $learner) {
            return response()->json(['data' => []]);
        }

        $certificates = $this->certificateService->listForLearner($learner->id);

        return response()->json($certificates);
    }

    public function batchPdf(Request $request): JsonResponse
    {
        $request->validate(['certificate_ids' => ['required', 'array', 'min:1']]);
        $ids = $request->input('certificate_ids');
        $results = [];

        foreach ($ids as $id) {
            $cert = Certificate::find($id);
            if (! $cert) {
                continue;
            }

            if (! $cert->pdf_file_path) {
                $this->pdfService->generate($cert);
                $cert->refresh();
            }

            $results[] = [
                'id' => $cert->id,
                'certificate_no' => $cert->certificate_no,
                'pdf_path' => $cert->pdf_file_path,
                'download_url' => '/storage/'.$cert->pdf_file_path,
            ];
        }

        return response()->json(['data' => $results]);
    }

    public function reprintRequest(int $id, Request $request): JsonResponse
    {
        $request->validate(['reason' => ['required', 'string', 'max:500']]);

        $cert = Certificate::findOrFail($id);

        $cert->update([
            'reprint_reason' => $request->input('reason'),
            'reprinted_by' => $request->user()->id,
            'reprinted_at' => now(),
        ]);

        $this->auditLogger->record('certificate.reprint_request', 'certificate', $id, after: $cert->toArray());

        return response()->json(['data' => $cert]);
    }
}
