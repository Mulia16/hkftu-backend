<?php

namespace Modules\Reporting\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class ReportPdfService
{
    public function generateAttendanceSheet(array $meta, array $students, array $sessions): string
    {
        $html = $this->buildAttendanceSheet($meta, $students, $sessions);

        return $this->renderAndSave($html, 'attendance_sheet', 'A4', 'landscape');
    }

    public function generateNameLabels(array $students): string
    {
        $html = $this->buildNameLabels($students);

        return $this->renderAndSave($html, 'name_labels', 'A4', 'portrait');
    }

    public function generateSignInSheet(array $meta, array $sessions): string
    {
        $html = $this->buildSignInSheet($meta, $sessions);

        return $this->renderAndSave($html, 'instructor_sign_in', 'A4', 'landscape');
    }

    public function generateCertificateApplication(array $meta, array $students): string
    {
        $html = $this->buildCertificateApplication($meta, $students);

        return $this->renderAndSave($html, 'certificate_application', 'A4', 'portrait');
    }

    private function renderAndSave(string $html, string $prefix, string $paper = 'A4', string $orientation = 'portrait'): string
    {
        $pdf = Pdf::loadHTML($html)->setPaper($paper, $orientation);

        $filename = 'reports/' . $prefix . '_' . now()->format('YmdHis') . '.pdf';
        $path = storage_path('app/public/' . $filename);

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        file_put_contents($path, $pdf->output());

        return $filename;
    }

    private function buildAttendanceSheet(array $meta, array $students, array $sessions): string
    {
        $sessionHeaders = '';
        foreach ($sessions as $s) {
            $sessionHeaders .= '<th class="sess">' . e($s['date']) . '<br><small>' . e($s['time']) . '</small></th>';
        }

        $studentRows = '';
        $num = 0;
        foreach ($students as $student) {
            $num++;
            $sessionCells = '';
            foreach ($sessions as $s) {
                $sessionCells .= '<td class="sess"></td>';
            }
            $studentRows .= <<<HTML
            <tr>
                <td class="num">{$num}</td>
                <td class="name">{$student['name_en']}</td>
                <td class="name-zh">{$student['name_zh']}</td>
                <td class="phone">{$student['phone']}</td>
                {$sessionCells}
                <td class="remark"></td>
            </tr>
            HTML;
        }

        $totalSessions = count($sessions);
        $emptyCols = '';
        for ($i = 0; $i < $totalSessions; $i++) {
            $emptyCols .= '<td class="sess"></td>';
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; font-size: 10px; margin: 15px; }
.header { text-align: center; margin-bottom: 15px; }
.header h2 { margin: 0; font-size: 16px; color: #E60013; }
.header p { margin: 2px 0; font-size: 11px; color: #666; }
.meta { margin-bottom: 15px; font-size: 11px; }
.meta-row { display: flex; justify-content: space-between; }
.meta-item { margin-bottom: 4px; }
.meta-label { font-weight: bold; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
th { background: #f0f0f0; font-size: 9px; text-align: center; }
.num { width: 30px; text-align: center; }
.name { width: 140px; }
.name-zh { width: 80px; }
.phone { width: 100px; }
.sess { width: 55px; text-align: center; font-size: 8px; }
.remark { width: 80px; }
tr { height: 28px; }
.footer { margin-top: 20px; display: flex; justify-content: space-between; font-size: 10px; }
.signature { width: 200px; border-top: 1px solid #333; margin-top: 30px; padding-top: 4px; text-align: center; }
</style>
</head>
<body>
<div class="header">
    <h2>HKFTU Continuing Education Centre</h2>
    <p>Attendance Sheet</p>
</div>

<div class="meta">
    <div class="meta-row">
        <div class="meta-item"><span class="meta-label">Course:</span> {$meta['course']}</div>
        <div class="meta-item"><span class="meta-label">Class Code:</span> {$meta['class_code']}</div>
    </div>
    <div class="meta-row">
        <div class="meta-item"><span class="meta-label">Centre:</span> {$meta['centre']}</div>
        <div class="meta-item"><span class="meta-label">Instructor:</span> {$meta['instructor']}</div>
    </div>
    <div class="meta-row">
        <div class="meta-item"><span class="meta-label">Period:</span> {$meta['start_date']} — {$meta['end_date']}</div>
        <div class="meta-item"><span class="meta-label">Total Students:</span> {$meta['student_count']}</div>
    </div>
</div>

<table>
    <thead>
        <tr>
            <th class="num">No</th>
            <th class="name">Name (EN)</th>
            <th class="name-zh">Name (ZH)</th>
            <th class="phone">Phone</th>
            {$sessionHeaders}
            <th class="remark">Remark</th>
        </tr>
    </thead>
    <tbody>
        {$studentRows}
    </tbody>
</table>

<div class="footer">
    <div class="signature">Instructor Signature</div>
    <div class="signature">Centre Manager Signature</div>
</div>
</body>
</html>
HTML;
    }

    private function buildNameLabels(array $students): string
    {
        $labels = '';
        $count = 0;
        foreach ($students as $student) {
            $nameEn = e($student['name_en']);
            $nameZh = e($student['name_zh'] ?? '');
            $course = e($student['course'] ?? '');
            $display = $nameZh ? "{$nameEn} / {$nameZh}" : $nameEn;

            $labels .= <<<HTML
            <div class="label">
                <div class="label-name">{$display}</div>
                <div class="label-course">{$course}</div>
            </div>
            HTML;
            $count++;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; margin: 10px; }
.header { text-align: center; margin-bottom: 15px; }
.header h2 { margin: 0; font-size: 14px; color: #E60013; }
.labels { display: flex; flex-wrap: wrap; gap: 8px; }
.label { width: 230px; height: 65px; border: 1px solid #999; padding: 8px 12px; display: flex; flex-direction: column; justify-content: center; page-break-inside: avoid; }
.label-name { font-size: 14px; font-weight: bold; }
.label-course { font-size: 10px; color: #666; margin-top: 4px; }
</style>
</head>
<body>
<div class="header">
    <h2>HKFTU — Student Name Labels</h2>
    <p>Total: {$count} students</p>
</div>
<div class="labels">
    {$labels}
</div>
</body>
</html>
HTML;
    }

    private function buildSignInSheet(array $meta, array $sessions): string
    {
        $sessionRows = '';
        $num = 0;
        foreach ($sessions as $s) {
            $num++;
            $date = e($s['date']);
            $time = e($s['time']);
            $sessionRows .= <<<HTML
            <tr>
                <td class="num">{$num}</td>
                <td class="date">{$date}</td>
                <td class="time">{$time}</td>
                <td class="sign"></td>
                <td class="remark"></td>
            </tr>
            HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; font-size: 11px; margin: 20px; }
.header { text-align: center; margin-bottom: 20px; }
.header h2 { margin: 0; font-size: 16px; color: #E60013; }
.meta { margin-bottom: 15px; }
.meta-item { margin-bottom: 4px; }
.meta-label { font-weight: bold; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #333; padding: 6px 10px; }
th { background: #f0f0f0; text-align: center; }
.num { width: 40px; text-align: center; }
.date { width: 120px; }
.time { width: 100px; }
.sign { width: 200px; height: 35px; }
.remark { width: 150px; }
</style>
</head>
<body>
<div class="header">
    <h2>HKFTU Continuing Education Centre</h2>
    <p>Instructor Sign-In Sheet</p>
</div>

<div class="meta">
    <div class="meta-item"><span class="meta-label">Instructor:</span> {$meta['instructor']}</div>
    <div class="meta-item"><span class="meta-label">Course:</span> {$meta['course']}</div>
    <div class="meta-item"><span class="meta-label">Class Code:</span> {$meta['class_code']}</div>
    <div class="meta-item"><span class="meta-label">Centre:</span> {$meta['centre']}</div>
    <div class="meta-item"><span class="meta-label">Period:</span> {$meta['start_date']} — {$meta['end_date']}</div>
</div>

<table>
    <thead>
        <tr>
            <th class="num">No</th>
            <th class="date">Date</th>
            <th class="time">Time</th>
            <th class="sign">Instructor Signature</th>
            <th class="remark">Remark</th>
        </tr>
    </thead>
    <tbody>
        {$sessionRows}
    </tbody>
</table>
</body>
</html>
HTML;
    }

    private function buildCertificateApplication(array $meta, array $students): string
    {
        $rows = '';
        $num = 0;
        foreach ($students as $s) {
            $num++;
            $nameEn = e($s['name_en']);
            $nameZh = e($s['name_zh'] ?? '');
            $idNo = e($s['id_no'] ?? '***');
            $attended = e($s['attended'] ?? 0);
            $total = e($s['total_sessions'] ?? 0);
            $rate = e($s['attendance_rate'] ?? 0);
            $eligible = $rate >= 75 ? '✓' : '✗';

            $rows .= <<<HTML
            <tr>
                <td class="num">{$num}</td>
                <td class="name">{$nameEn}</td>
                <td class="name-zh">{$nameZh}</td>
                <td class="id">{$idNo}</td>
                <td class="num">{$attended}/{$total}</td>
                <td class="num">{$rate}%</td>
                <td class="num">{$eligible}</td>
                <td class="sign"></td>
            </tr>
            HTML;
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; font-size: 10px; margin: 15px; }
.header { text-align: center; margin-bottom: 15px; }
.header h2 { margin: 0; font-size: 16px; color: #E60013; }
.meta { margin-bottom: 15px; font-size: 11px; }
.meta-item { margin-bottom: 4px; }
.meta-label { font-weight: bold; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #333; padding: 4px 6px; }
th { background: #f0f0f0; font-size: 9px; text-align: center; }
.num { width: 30px; text-align: center; }
.name { width: 120px; }
.name-zh { width: 70px; }
.id { width: 90px; font-family: monospace; font-size: 9px; }
.sign { width: 100px; height: 25px; }
.note { margin-top: 15px; font-size: 10px; color: #666; }
</style>
</head>
<body>
<div class="header">
    <h2>HKFTU Continuing Education Centre</h2>
    <p>Certificate Application Form</p>
</div>

<div class="meta">
    <div class="meta-item"><span class="meta-label">Course:</span> {$meta['course']}</div>
    <div class="meta-item"><span class="meta-label">Class Code:</span> {$meta['class_code']}</div>
    <div class="meta-item"><span class="meta-label">Centre:</span> {$meta['centre']}</div>
    <div class="meta-item"><span class="meta-label">Minimum Attendance:</span> 75%</div>
</div>

<table>
    <thead>
        <tr>
            <th class="num">No</th>
            <th class="name">Name (EN)</th>
            <th class="name-zh">Name (ZH)</th>
            <th class="id">ID No</th>
            <th class="num">Attended</th>
            <th class="num">Rate</th>
            <th class="num">Eligible</th>
            <th class="sign">Staff Sign</th>
        </tr>
    </thead>
    <tbody>
        {$rows}
    </tbody>
</table>

<div class="note">
    <p>Prepared by: _________________ &nbsp;&nbsp; Date: _________________ &nbsp;&nbsp; Approved by: _________________</p>
</div>
</body>
</html>
HTML;
    }
}
