<?php

namespace Modules\Certificate\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CertificateRecordSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        DB::table('certificate.certificate_templates')->insert([
            [
                'code' => 'CERT-STD',
                'name' => 'Standard Certificate',
                'variables_json' => json_encode(['learner_name', 'course_name', 'completion_date', 'attendance_rate']),
                'status' => 'active',
                'version_no' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CERT-PROF',
                'name' => 'Professional Certificate',
                'variables_json' => json_encode(['learner_name', 'course_name', 'completion_date', 'attendance_rate', 'instructor_name', 'hours_completed']),
                'status' => 'active',
                'version_no' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'CERT-MERIT',
                'name' => 'Certificate of Merit',
                'variables_json' => json_encode(['learner_name', 'course_name', 'completion_date', 'attendance_rate', 'grade']),
                'status' => 'active',
                'version_no' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $enrolments = DB::table('enrolment.enrolments')->where('status', 'confirmed')->limit(3)->get();

        if ($enrolments->isEmpty()) {
            return;
        }

        foreach ($enrolments as $i => $enrolment) {
            DB::table('certificate.certificates')->insert([
                'certificate_no' => sprintf('CERT-2026-%05d', $i + 1),
                'enrolment_id' => $enrolment->id,
                'template_id' => 1,
                'issued_at' => $now,
                'issued_by' => 1,
                'status' => 'issued',
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
