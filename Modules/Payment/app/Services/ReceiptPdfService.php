<?php

namespace Modules\Payment\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Modules\Payment\Models\Receipt;

class ReceiptPdfService
{
    public function generate(Receipt $receipt): string
    {
        $receipt->load([
            'enrolment.learner',
            'enrolment.courseClass.course.subject',
            'enrolment.courseClass.centre',
            'paymentIntent',
        ]);

        $learner = $receipt->enrolment->learner;
        $course = $receipt->enrolment->courseClass->course->subject;
        $centre = $receipt->enrolment->courseClass->centre;
        $intent = $receipt->paymentIntent;

        $html = $this->buildHtml(
            receiptNo: $receipt->receipt_no,
            issuedAt: $receipt->issued_at->format('d F Y'),
            learnerName: $learner->name_en ?? 'Unknown',
            learnerNameZh: $learner->name_zh ?? '',
            learnerEmail: $learner->email ?? '',
            learnerPhone: $learner->phone ?? '',
            courseName: $course->name ?? 'Unknown',
            courseCode: $receipt->enrolment->courseClass->course->course_code ?? '',
            centreName: $centre->name ?? '',
            amount: number_format((float) $receipt->amount, 2),
            paymentMethod: $intent->method ?? 'N/A',
            transactionRef: $intent->gateway_ref ?? 'N/A',
        );

        $pdf = Pdf::loadHTML($html)->setPaper('A4', 'portrait');

        $filename = 'receipts/' . $receipt->receipt_no . '.pdf';
        $path = storage_path('app/public/' . $filename);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $pdf->output());

        $receipt->update(['pdf_file_path' => $filename]);

        return $filename;
    }

    public function getPath(Receipt $receipt): ?string
    {
        if (!$receipt->pdf_file_path) {
            return null;
        }

        $path = storage_path('app/public/' . $receipt->pdf_file_path);

        return file_exists($path) ? $path : null;
    }

    private function buildHtml(
        string $receiptNo,
        string $issuedAt,
        string $learnerName,
        string $learnerNameZh,
        string $learnerEmail,
        string $learnerPhone,
        string $courseName,
        string $courseCode,
        string $centreName,
        string $amount,
        string $paymentMethod,
        string $transactionRef,
    ): string {
        $learnerDisplay = $learnerNameZh ? "{$learnerName} / {$learnerNameZh}" : $learnerName;

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; margin: 0; padding: 30px; color: #333; font-size: 13px; }
.header { text-align: center; border-bottom: 2px solid #E60013; padding-bottom: 15px; margin-bottom: 20px; }
.logo { font-size: 20px; font-weight: bold; color: #E60013; }
.org { font-size: 11px; color: #666; margin-top: 4px; }
.title { font-size: 18px; font-weight: bold; text-align: center; margin: 20px 0; }
.meta { display: flex; justify-content: space-between; margin-bottom: 20px; }
.meta-item { font-size: 12px; }
.meta-label { color: #999; font-size: 10px; text-transform: uppercase; }
.meta-value { font-weight: bold; margin-top: 2px; }
.section { margin-bottom: 20px; }
.section-title { font-size: 11px; text-transform: uppercase; color: #999; border-bottom: 1px solid #eee; padding-bottom: 4px; margin-bottom: 10px; }
.row { display: flex; justify-content: space-between; padding: 6px 0; border-bottom: 1px solid #f5f5f5; }
.row-label { color: #666; }
.row-value { font-weight: bold; }
.amount-box { background: #f8f8f8; border: 1px solid #ddd; padding: 15px; text-align: center; margin: 20px 0; }
.amount-label { font-size: 11px; color: #999; }
.amount-value { font-size: 28px; font-weight: bold; color: #E60013; }
.footer { margin-top: 30px; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 15px; }
</style>
</head>
<body>
<div class="header">
    <div class="logo">HKFTU Continuing Education Centre</div>
    <div class="org">HKFTU Training System</div>
</div>

<div class="title">PAYMENT RECEIPT</div>

<div class="meta">
    <div class="meta-item">
        <div class="meta-label">Receipt No</div>
        <div class="meta-value">{$receiptNo}</div>
    </div>
    <div class="meta-item" style="text-align: right;">
        <div class="meta-label">Date Issued</div>
        <div class="meta-value">{$issuedAt}</div>
    </div>
</div>

<div class="section">
    <div class="section-title">Student Information</div>
    <div class="row">
        <span class="row-label">Name</span>
        <span class="row-value">{$learnerDisplay}</span>
    </div>
    <div class="row">
        <span class="row-label">Email</span>
        <span class="row-value">{$learnerEmail}</span>
    </div>
    <div class="row">
        <span class="row-label">Phone</span>
        <span class="row-value">{$learnerPhone}</span>
    </div>
</div>

<div class="section">
    <div class="section-title">Course Information</div>
    <div class="row">
        <span class="row-label">Course</span>
        <span class="row-value">{$courseName}</span>
    </div>
    <div class="row">
        <span class="row-label">Course Code</span>
        <span class="row-value">{$courseCode}</span>
    </div>
    <div class="row">
        <span class="row-label">Centre</span>
        <span class="row-value">{$centreName}</span>
    </div>
</div>

<div class="amount-box">
    <div class="amount-label">AMOUNT PAID</div>
    <div class="amount-value">HKD {$amount}</div>
</div>

<div class="section">
    <div class="section-title">Payment Details</div>
    <div class="row">
        <span class="row-label">Payment Method</span>
        <span class="row-value">{$paymentMethod}</span>
    </div>
    <div class="row">
        <span class="row-label">Transaction Reference</span>
        <span class="row-value">{$transactionRef}</span>
    </div>
</div>

<div class="footer">
    <p>This is a computer-generated receipt. No signature is required.</p>
    <p>HKFTU Continuing Education Centre | General Enquiry: +852 2893 3993</p>
</div>
</body>
</html>
HTML;
    }
}
