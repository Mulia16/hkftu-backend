<?php

namespace Modules\Certificate\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Modules\Certificate\Models\Certificate;
use Modules\Certificate\Models\CertificateTemplate;

class CertificateService
{
    public function __construct(
        private CertificateEligibilityService $eligibilityService,
    ) {}

    public function issueBatch(int $classId, int $templateId, int $issuedBy, bool $override = false, ?string $overrideReason = null): array
    {
        $template = CertificateTemplate::findOrFail($templateId);
        $eligibility = $this->eligibilityService->calculateEligibility($classId);

        $eligibleEnrolments = $override
            ? $eligibility
            : array_filter($eligibility, fn ($e) => $e['eligible']);

        $issued = [];
        $skipped = [];

        foreach ($eligibleEnrolments as $entry) {
            $enrolmentId = $entry['enrolment_id'];

            $existing = Certificate::where('enrolment_id', $enrolmentId)
                ->where('status', 'issued')
                ->first();

            if ($existing) {
                $skipped[] = ['enrolment_id' => $enrolmentId, 'reason' => 'already issued'];

                continue;
            }

            $cert = Certificate::create([
                'certificate_no' => $this->generateCertificateNo(),
                'enrolment_id' => $enrolmentId,
                'template_id' => $templateId,
                'issued_at' => now(),
                'issued_by' => $issuedBy,
                'status' => 'issued',
            ]);

            $issued[] = $cert;
        }

        return [
            'issued' => $issued,
            'skipped' => $skipped,
            'total_eligible' => count($eligibleEnrolments),
            'total_issued' => count($issued),
            'total_skipped' => count($skipped),
        ];
    }

    public function issueSingle(int $enrolmentId, int $templateId, int $issuedBy, bool $override = false, ?string $overrideReason = null): Certificate
    {
        $template = CertificateTemplate::findOrFail($templateId);

        if (! $override) {
            $eligible = $this->eligibilityService->isEligible($enrolmentId);
            if (! $eligible) {
                throw new \RuntimeException('Learner is not eligible for certificate. Use override to force issuance.');
            }
        }

        $existing = Certificate::where('enrolment_id', $enrolmentId)
            ->where('status', 'issued')
            ->first();

        if ($existing) {
            throw new \RuntimeException('Certificate already issued for this enrolment. Use reprint instead.');
        }

        return Certificate::create([
            'certificate_no' => $this->generateCertificateNo(),
            'enrolment_id' => $enrolmentId,
            'template_id' => $templateId,
            'issued_at' => now(),
            'issued_by' => $issuedBy,
            'status' => 'issued',
        ]);
    }

    public function reprint(int $certificateId, int $reprintedBy, ?string $reason = null): Certificate
    {
        $cert = Certificate::findOrFail($certificateId);

        if ($cert->status === 'voided') {
            throw new \RuntimeException('Cannot reprint a voided certificate.');
        }

        $cert->update([
            'status' => 'reprinted',
            'reprint_reason' => $reason,
            'reprinted_by' => $reprintedBy,
            'reprinted_at' => now(),
        ]);

        $newCert = Certificate::create([
            'certificate_no' => $this->generateCertificateNo(),
            'enrolment_id' => $cert->enrolment_id,
            'template_id' => $cert->template_id,
            'issued_at' => now(),
            'issued_by' => $reprintedBy,
            'status' => 'issued',
        ]);

        return $newCert;
    }

    public function listAll(?string $status = null): LengthAwarePaginator
    {
        return Certificate::with(['enrolment.learner', 'enrolment.courseClass.course.subject', 'template', 'issuer'])
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderByDesc('issued_at')
            ->paginate(25);
    }

    public function listForLearner(int $learnerId): LengthAwarePaginator
    {
        return Certificate::with(['template', 'enrolment.courseClass.course.subject'])
            ->whereHas('enrolment', fn ($q) => $q->where('learner_id', $learnerId))
            ->orderByDesc('issued_at')
            ->paginate(25);
    }

    private function generateCertificateNo(): string
    {
        $year = now()->format('Y');

        return DB::transaction(function () use ($year) {
            $last = DB::table('certificate.certificates')
                ->where('certificate_no', 'like', "CERT-{$year}-%")
                ->orderByDesc('certificate_no')
                ->value('certificate_no');

            $seq = $last ? (int) substr($last, -5) + 1 : 1;

            return sprintf('CERT-%s-%05d', $year, $seq);
        });
    }
}
