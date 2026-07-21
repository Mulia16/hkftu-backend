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

    public function generateCertificatePrint(array $meta, array $students): string
    {
        $html = $this->buildCertificatePrint($meta, $students);

        return $this->renderAndSave($html, 'certificate_print', 'A4', 'landscape');
    }

    public function generateInstructorContract(array $meta, array $contract): string
    {
        $html = $this->buildInstructorContract($meta, $contract);

        return $this->renderAndSave($html, 'instructor_contract', 'A4', 'portrait');
    }

    public function generateAdvancedCourseNotice(array $meta, array $course): string
    {
        $html = $this->buildAdvancedCourseNotice($meta, $course);

        return $this->renderAndSave($html, 'advanced_course_notice', 'A4', 'portrait');
    }

    public function generateAdvancedInstructorNotice(array $meta, array $notice): string
    {
        $html = $this->buildAdvancedInstructorNotice($meta, $notice);

        return $this->renderAndSave($html, 'advanced_instructor_notice', 'A4', 'portrait');
    }

    public function generateInstructorCommunication(array $meta, array $data): string
    {
        $html = $this->buildInstructorCommunication($meta, $data);

        return $this->renderAndSave($html, 'instructor_communication', 'A4', 'landscape');
    }

    public function generateInstructorCheque(array $meta, array $payment): string
    {
        $html = $this->buildInstructorCheque($meta, $payment);

        return $this->renderAndSave($html, 'instructor_cheque', 'A4', 'portrait');
    }

    private function renderAndSave(string $html, string $prefix, string $paper = 'A4', string $orientation = 'portrait'): string
    {
        $pdf = Pdf::loadHTML($html)->setPaper($paper, $orientation);

        $filename = 'reports/' . $prefix . '_' . now()->format('YmdHis') . '.pdf';

        Storage::disk('local')->put($filename, $pdf->output());

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

    private function buildCertificatePrint(array $meta, array $students): string
    {
        $certs = '';
        $num = 0;
        foreach ($students as $s) {
            $num++;
            $nameEn = e($s['name_en']);
            $nameZh = e($s['name_zh'] ?? '');
            $course = e($s['course'] ?? '');
            $classCode = e($s['class_code'] ?? '');
            $completionDate = e($s['completion_date'] ?? '');
            $attendanceRate = e($s['attendance_rate'] ?? 0);

            $certs .= <<<HTML
            <div class="cert-page">
                <div class="cert-border">
                    <div class="cert-logo">
                        <h2>HKFTU</h2>
                        <p>Continuing Education Centre</p>
                    </div>
                    <div class="cert-title">CERTIFICATE OF COMPLETION</div>
                    <div class="cert-body">
                        <p>This is to certify that</p>
                        <div class="cert-name">{$nameEn}</div>
                        <div class="cert-name-zh">{$nameZh}</div>
                        <p>has successfully completed the course</p>
                        <div class="cert-course">{$course}</div>
                        <p>Class Code: {$classCode}</p>
                        <p>Completion Date: {$completionDate} &nbsp;&nbsp;&nbsp; Attendance Rate: {$attendanceRate}%</p>
                    </div>
                    <div class="cert-signatures">
                        <div class="cert-sig">
                            <div class="cert-sig-line"></div>
                            <p>Instructor</p>
                        </div>
                        <div class="cert-sig">
                            <div class="cert-sig-line"></div>
                            <p>Centre Manager</p>
                        </div>
                    </div>
                </div>
            </div>
            HTML;
        }

        $courseName = e($meta['course'] ?? '');
        $classCode = e($meta['class_code'] ?? '');
        $centre = e($meta['centre'] ?? '');

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
.cert-page { page-break-after: always; padding: 20px; }
.cert-page:last-child { page-break-after: avoid; }
.cert-border { border: 4px double #E60013; padding: 30px 40px; min-height: 500px; position: relative; }
.cert-logo { text-align: center; margin-bottom: 10px; }
.cert-logo h2 { margin: 0; font-size: 28px; color: #E60013; letter-spacing: 4px; }
.cert-logo p { margin: 2px 0; font-size: 12px; color: #666; }
.cert-title { text-align: center; font-size: 22px; font-weight: bold; color: #333; margin: 20px 0; letter-spacing: 3px; border-bottom: 2px solid #E60013; padding-bottom: 10px; }
.cert-body { text-align: center; margin: 20px 40px; }
.cert-body p { font-size: 13px; color: #555; margin: 8px 0; }
.cert-name { font-size: 24px; font-weight: bold; color: #333; margin: 15px 0 5px; border-bottom: 1px solid #ccc; display: inline-block; padding: 0 40px 5px; }
.cert-name-zh { font-size: 18px; color: #555; margin-bottom: 15px; }
.cert-course { font-size: 18px; font-weight: bold; color: #E60013; margin: 10px 0; }
.cert-signatures { display: flex; justify-content: space-around; margin-top: 50px; }
.cert-sig { text-align: center; width: 200px; }
.cert-sig-line { border-top: 1px solid #333; margin-bottom: 5px; }
.cert-sig p { font-size: 11px; color: #666; margin: 0; }
.meta { text-align: center; margin-bottom: 10px; font-size: 11px; color: #999; }
</style>
</head>
<body>
<div class="meta">
    <p>Course: {$courseName} &nbsp;|&nbsp; Class: {$classCode} &nbsp;|&nbsp; Centre: {$centre}</p>
</div>
{$certs}
</body>
</html>
HTML;
    }

    private function buildInstructorContract(array $meta, array $contract): string
    {
        $instructorName = e($contract['instructor_name']);
        $course = e($contract['course']);
        $classCode = e($contract['class_code']);
        $centre = e($contract['centre']);
        $startDate = e($contract['start_date']);
        $endDate = e($contract['end_date']);
        $feeAmount = e($contract['fee_amount']);
        $feeType = e($contract['fee_type']);
        $totalSessions = e($contract['total_sessions']);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; font-size: 11px; margin: 30px; line-height: 1.6; }
.header { text-align: center; margin-bottom: 25px; }
.header h2 { margin: 0; font-size: 18px; color: #E60013; }
.header p { margin: 2px 0; font-size: 12px; color: #666; }
.section { margin-bottom: 15px; }
.section-title { font-size: 13px; font-weight: bold; color: #E60013; border-bottom: 1px solid #E60013; padding-bottom: 3px; margin-bottom: 8px; }
.detail-row { display: flex; margin-bottom: 4px; }
.detail-label { font-weight: bold; width: 160px; }
.detail-value { flex: 1; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #333; padding: 6px 10px; text-align: left; }
th { background: #f0f0f0; width: 160px; }
.terms { margin: 15px 0; }
.terms ol { padding-left: 20px; }
.terms li { margin-bottom: 6px; }
.signatures { display: flex; justify-content: space-between; margin-top: 40px; }
.signature { width: 220px; text-align: center; }
.signature-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; }
</style>
</head>
<body>
<div class="header">
    <h2>HKFTU Continuing Education Centre</h2>
    <p>Instructor Assignment Contract</p>
</div>

<div class="section">
    <div class="section-title">Instructor Details</div>
    <table>
        <tr><th>Instructor Name</th><td>{$instructorName}</td></tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Course Details</div>
    <table>
        <tr><th>Course</th><td>{$course}</td></tr>
        <tr><th>Class Code</th><td>{$classCode}</td></tr>
        <tr><th>Centre</th><td>{$centre}</td></tr>
        <tr><th>Start Date</th><td>{$startDate}</td></tr>
        <tr><th>End Date</th><td>{$endDate}</td></tr>
        <tr><th>Total Sessions</th><td>{$totalSessions}</td></tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Fee Terms</div>
    <table>
        <tr><th>Fee Type</th><td>{$feeType}</td></tr>
        <tr><th>Fee Amount</th><td>HK\$ {$feeAmount}</td></tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Terms &amp; Conditions</div>
    <div class="terms">
        <ol>
            <li>The instructor shall conduct all sessions as scheduled and maintain professional standards.</li>
            <li>The instructor shall prepare all teaching materials and lesson plans required for the course.</li>
            <li>The instructor shall maintain accurate attendance records for each session.</li>
            <li>Payment shall be made upon satisfactory completion of all sessions and submission of required documentation.</li>
            <li>Either party may terminate this contract with written notice of at least 7 days.</li>
            <li>The instructor shall not disclose confidential information of HKFTU or its students.</li>
            <li>This contract is subject to the policies and regulations of HKFTU Continuing Education Centre.</li>
        </ol>
    </div>
</div>

<div class="signatures">
    <div class="signature">
        <div class="signature-line">Instructor Signature</div>
    </div>
    <div class="signature">
        <div class="signature-line">Centre Manager Signature</div>
    </div>
    <div class="signature">
        <div class="signature-line">Date</div>
    </div>
</div>
</body>
</html>
HTML;
    }

    private function buildAdvancedCourseNotice(array $meta, array $course): string
    {
        $courseName = e($course['course_name']);
        $classCode = e($course['class_code']);
        $centre = e($course['centre']);
        $startDate = e($course['start_date']);
        $endDate = e($course['end_date']);
        $tuitionFee = e($course['tuition_fee']);
        $schedule = e($course['schedule']);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; font-size: 11px; margin: 30px; line-height: 1.6; }
.header { text-align: center; margin-bottom: 25px; }
.header h2 { margin: 0; font-size: 18px; color: #E60013; }
.header p { margin: 2px 0; font-size: 12px; color: #666; }
.notice-title { text-align: center; font-size: 16px; font-weight: bold; color: #333; margin: 15px 0; }
.section { margin-bottom: 15px; }
.section-title { font-size: 13px; font-weight: bold; color: #E60013; border-bottom: 1px solid #E60013; padding-bottom: 3px; margin-bottom: 8px; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #333; padding: 8px 12px; text-align: left; }
th { background: #f0f0f0; width: 180px; }
.info-box { background: #fff8f8; border: 1px solid #E60013; padding: 15px; margin: 15px 0; }
.info-box p { margin: 4px 0; }
.contact { margin-top: 20px; padding: 15px; background: #f9f9f9; border: 1px solid #ddd; }
.contact p { margin: 4px 0; }
.footer { margin-top: 25px; text-align: center; font-size: 10px; color: #999; }
</style>
</head>
<body>
<div class="header">
    <h2>HKFTU Continuing Education Centre</h2>
    <p>Advanced Course Enrolment Notice</p>
</div>

<div class="notice-title">ADVANCED COURSE NOTICE</div>

<div class="section">
    <div class="section-title">Course Details</div>
    <table>
        <tr><th>Course Name</th><td>{$courseName}</td></tr>
        <tr><th>Class Code</th><td>{$classCode}</td></tr>
        <tr><th>Centre</th><td>{$centre}</td></tr>
        <tr><th>Start Date</th><td>{$startDate}</td></tr>
        <tr><th>End Date</th><td>{$endDate}</td></tr>
        <tr><th>Schedule</th><td>{$schedule}</td></tr>
        <tr><th>Tuition Fee</th><td>HK\$ {$tuitionFee}</td></tr>
    </table>
</div>

<div class="info-box">
    <p><strong>Registration Information</strong></p>
    <p>Students who have completed the prerequisite course are eligible to enrol in this advanced course.</p>
    <p>Please register at the centre office or contact us for enrolment details.</p>
    <p>Places are limited and allocated on a first-come, first-served basis.</p>
</div>

<div class="contact">
    <p><strong>Contact Details</strong></p>
    <p>Centre: {$centre}</p>
    <p>Please contact the centre office during opening hours for enquiries.</p>
</div>

<div class="footer">
    <p>HKFTU Continuing Education Centre</p>
</div>
</body>
</html>
HTML;
    }

    private function buildAdvancedInstructorNotice(array $meta, array $notice): string
    {
        $instructorName = e($notice['instructor_name']);
        $course = e($notice['course']);
        $classCode = e($notice['class_code']);
        $centre = e($notice['centre']);
        $startDate = e($notice['start_date']);
        $endDate = e($notice['end_date']);
        $schedule = e($notice['schedule']);
        $studentCount = e($notice['student_count']);

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; font-size: 11px; margin: 30px; line-height: 1.6; }
.header { text-align: center; margin-bottom: 25px; }
.header h2 { margin: 0; font-size: 18px; color: #E60013; }
.header p { margin: 2px 0; font-size: 12px; color: #666; }
.notice-title { text-align: center; font-size: 16px; font-weight: bold; color: #333; margin: 15px 0; }
.section { margin-bottom: 15px; }
.section-title { font-size: 13px; font-weight: bold; color: #E60013; border-bottom: 1px solid #E60013; padding-bottom: 3px; margin-bottom: 8px; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #333; padding: 8px 12px; text-align: left; }
th { background: #f0f0f0; width: 180px; }
.note { margin: 15px 0; padding: 12px; background: #f9f9f9; border-left: 3px solid #E60013; }
.signature { width: 220px; border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; text-align: center; }
</style>
</head>
<body>
<div class="header">
    <h2>HKFTU Continuing Education Centre</h2>
    <p>Advanced Class Assignment Notice</p>
</div>

<div class="notice-title">INSTRUCTOR ASSIGNMENT NOTICE</div>

<div class="section">
    <div class="section-title">Instructor Details</div>
    <table>
        <tr><th>Instructor Name</th><td>{$instructorName}</td></tr>
    </table>
</div>

<div class="section">
    <div class="section-title">Class Details</div>
    <table>
        <tr><th>Course</th><td>{$course}</td></tr>
        <tr><th>Class Code</th><td>{$classCode}</td></tr>
        <tr><th>Centre</th><td>{$centre}</td></tr>
        <tr><th>Start Date</th><td>{$startDate}</td></tr>
        <tr><th>End Date</th><td>{$endDate}</td></tr>
        <tr><th>Schedule</th><td>{$schedule}</td></tr>
        <tr><th>Expected Students</th><td>{$studentCount}</td></tr>
    </table>
</div>

<div class="note">
    <p>Please prepare the course materials and lesson plans in advance. Ensure attendance records are maintained for each session.</p>
</div>

<div class="signature">Centre Manager Signature</div>
</body>
</html>
HTML;
    }

    private function buildInstructorCommunication(array $meta, array $data): string
    {
        $rows = '';
        $num = 0;
        foreach ($data as $d) {
            $num++;
            $instructorName = e($d['instructor_name']);
            $phone = e($d['phone']);
            $email = e($d['email']);
            $course = e($d['course']);
            $classCode = e($d['class_code']);
            $centre = e($d['centre']);
            $startDate = e($d['start_date']);
            $sessions = e($d['sessions']);

            $rows .= <<<HTML
            <tr>
                <td class="num">{$num}</td>
                <td>{$instructorName}</td>
                <td>{$phone}</td>
                <td>{$email}</td>
                <td>{$course}</td>
                <td>{$classCode}</td>
                <td>{$centre}</td>
                <td>{$startDate}</td>
                <td class="num">{$sessions}</td>
                <td class="status"></td>
                <td class="status"></td>
                <td class="status"></td>
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
body { font-family: Arial, sans-serif; font-size: 9px; margin: 15px; }
.header { text-align: center; margin-bottom: 15px; }
.header h2 { margin: 0; font-size: 16px; color: #E60013; }
.header p { margin: 2px 0; font-size: 11px; color: #666; }
table { width: 100%; border-collapse: collapse; }
th, td { border: 1px solid #333; padding: 4px 6px; text-align: left; }
th { background: #f0f0f0; font-size: 8px; text-align: center; }
.num { width: 28px; text-align: center; }
.status { width: 55px; text-align: center; }
.remark { width: 70px; }
tr { height: 24px; }
.footer { margin-top: 15px; font-size: 10px; }
.signature { width: 200px; border-top: 1px solid #333; margin-top: 30px; padding-top: 4px; text-align: center; }
</style>
</head>
<body>
<div class="header">
    <h2>HKFTU Continuing Education Centre</h2>
    <p>Instructor Pre-Class Communication Record</p>
</div>

<table>
    <thead>
        <tr>
            <th class="num">No</th>
            <th>Instructor</th>
            <th>Phone</th>
            <th>Email</th>
            <th>Course</th>
            <th>Class Code</th>
            <th>Centre</th>
            <th>Start Date</th>
            <th class="num">Sessions</th>
            <th class="status">Contacted</th>
            <th class="status">Confirmed</th>
            <th class="status">Materials Sent</th>
            <th class="remark">Remark</th>
        </tr>
    </thead>
    <tbody>
        {$rows}
    </tbody>
</table>

<div class="footer">
    <div class="signature">Prepared by</div>
</div>
</body>
</html>
HTML;
    }

    private function buildInstructorCheque(array $meta, array $payment): string
    {
        $instructorName = e($payment['instructor_name']);
        $chequeNo = e($payment['cheque_no']);
        $amount = e($payment['amount']);
        $period = e($payment['period']);
        $paymentDate = e($payment['payment_date']);

        $courseRows = '';
        $totalAmount = 0;
        if (!empty($payment['courses'])) {
            $num = 0;
            foreach ($payment['courses'] as $c) {
                $num++;
                $cname = e($c['course_name'] ?? '');
                $ccode = e($c['class_code'] ?? '');
                $cfee = e($c['fee'] ?? 0);
                $totalAmount += (float) ($c['fee'] ?? 0);
                $courseRows .= <<<HTML
                <tr>
                    <td class="num">{$num}</td>
                    <td>{$cname}</td>
                    <td>{$ccode}</td>
                    <td class="amount">HK\$ {$cfee}</td>
                </tr>
                HTML;
            }
        }

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
body { font-family: Arial, sans-serif; font-size: 11px; margin: 30px; line-height: 1.6; }
.header { text-align: center; margin-bottom: 25px; }
.header h2 { margin: 0; font-size: 18px; color: #E60013; }
.header p { margin: 2px 0; font-size: 12px; color: #666; }
.section { margin-bottom: 15px; }
.section-title { font-size: 13px; font-weight: bold; color: #E60013; border-bottom: 1px solid #E60013; padding-bottom: 3px; margin-bottom: 8px; }
table { width: 100%; border-collapse: collapse; margin: 10px 0; }
th, td { border: 1px solid #333; padding: 8px 12px; text-align: left; }
th { background: #f0f0f0; }
.detail-table th { width: 180px; }
.num { width: 40px; text-align: center; }
.amount { width: 120px; text-align: right; }
.total-row td { font-weight: bold; background: #fff8f8; }
.payment-info { display: flex; justify-content: space-between; margin: 15px 0; }
.payment-box { border: 1px solid #333; padding: 12px 20px; text-align: center; }
.payment-box .label { font-size: 10px; color: #666; }
.payment-box .value { font-size: 18px; font-weight: bold; color: #E60013; }
.signatures { display: flex; justify-content: space-between; margin-top: 40px; }
.signature { width: 200px; text-align: center; }
.signature-line { border-top: 1px solid #333; margin-top: 50px; padding-top: 5px; }
</style>
</head>
<body>
<div class="header">
    <h2>HKFTU Continuing Education Centre</h2>
    <p>Instructor Payment — Cheque Record</p>
</div>

<div class="section">
    <div class="section-title">Instructor Details</div>
    <table class="detail-table">
        <tr><th>Instructor Name</th><td>{$instructorName}</td></tr>
        <tr><th>Cheque Number</th><td>{$chequeNo}</td></tr>
        <tr><th>Payment Period</th><td>{$period}</td></tr>
        <tr><th>Payment Date</th><td>{$paymentDate}</td></tr>
    </table>
</div>

<div class="payment-info">
    <div class="payment-box">
        <div class="label">Total Amount</div>
        <div class="value">HK\$ {$amount}</div>
    </div>
</div>

<div class="section">
    <div class="section-title">Payment Breakdown</div>
    <table>
        <thead>
            <tr>
                <th class="num">No</th>
                <th>Course</th>
                <th>Class Code</th>
                <th class="amount">Fee</th>
            </tr>
        </thead>
        <tbody>
            {$courseRows}
            <tr class="total-row">
                <td colspan="3" style="text-align:right;">Total</td>
                <td class="amount">HK\$ {$amount}</td>
            </tr>
        </tbody>
    </table>
</div>

<div class="signatures">
    <div class="signature">
        <div class="signature-line">Instructor Signature</div>
    </div>
    <div class="signature">
        <div class="signature-line">Prepared by</div>
    </div>
    <div class="signature">
        <div class="signature-line">Approved by</div>
    </div>
</div>
</body>
</html>
HTML;
    }
}
