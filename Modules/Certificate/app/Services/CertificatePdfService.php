<?php

namespace Modules\Certificate\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Modules\Certificate\Models\Certificate;

class CertificatePdfService
{
    public function generate(Certificate $certificate): string
    {
        $certificate->load(['enrolment.learner', 'enrolment.courseClass.course.subject', 'template']);

        $learner = $certificate->enrolment->learner;
        $course = $certificate->enrolment->courseClass->course->subject;

        $html = $this->buildHtml(
            certificateNo: $certificate->certificate_no,
            learnerName: $learner->name_en ?? 'Unknown',
            learnerNameZh: $learner->name_zh ?? '',
            courseName: $course->name ?? 'Unknown',
            completionDate: $certificate->issued_at->format('d F Y'),
            templateName: $certificate->template->name ?? 'Standard Certificate',
        );

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'landscape');

        $filename = 'certificates/'.$certificate->certificate_no.'.pdf';

        Storage::disk('local')->put($filename, $pdf->output());

        $certificate->update(['pdf_file_path' => $filename]);

        return $filename;
    }

    public function getPath(Certificate $certificate): ?string
    {
        if (! $certificate->pdf_file_path) {
            return null;
        }

        return Storage::disk('local')->exists($certificate->pdf_file_path)
            ? $certificate->pdf_file_path
            : null;
    }

    private function buildHtml(string $certificateNo, string $learnerName, string $learnerNameZh, string $courseName, string $completionDate, string $templateName): string
    {
        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: 'Georgia', serif; margin: 0; padding: 40px; }
.certificate { border: 3px double #333; padding: 60px; text-align: center; min-height: 500px; }
.header { font-size: 14px; letter-spacing: 4px; text-transform: uppercase; color: #666; margin-bottom: 10px; }
.title { font-size: 36px; font-weight: bold; color: #1a1a1a; margin: 20px 0; }
.subtitle { font-size: 14px; color: #666; margin-bottom: 30px; }
.name { font-size: 32px; font-weight: bold; color: #E60013; margin: 30px 0 10px; }
.name-zh { font-size: 24px; color: #666; margin-bottom: 20px; }
.course { font-size: 20px; color: #333; margin: 20px 0; }
.date { font-size: 16px; color: #666; margin-top: 30px; }
.cert-no { font-size: 12px; color: #999; margin-top: 20px; font-family: monospace; }
.footer { margin-top: 40px; display: flex; justify-content: space-between; }
.line { width: 200px; border-top: 1px solid #333; margin-top: 5px; }
.label { font-size: 11px; color: #999; }
</style>
</head>
<body>
<div class="certificate">
    <div class="header">HKFTU Continuing Education Centre</div>
    <div class="title">Certificate of Completion</div>
    <div class="subtitle">This is to certify that</div>
    <div class="name">{$learnerName}</div>
    <div class="name-zh">{$learnerNameZh}</div>
    <div class="subtitle">has successfully completed the course</div>
    <div class="course">{$courseName}</div>
    <div class="date">Date of Completion: {$completionDate}</div>
    <div class="cert-no">Certificate No: {$certificateNo}</div>
    <div class="footer">
        <div><div class="line"></div><div class="label">Centre Manager</div></div>
        <div><div class="line"></div><div class="label">Instructor</div></div>
    </div>
</div>
</body>
</html>
HTML;
    }
}
