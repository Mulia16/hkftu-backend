<?php

namespace Modules\Notification\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Notification\Models\NotificationLog;

class NotificationDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        NotificationLog::create([
            'channel' => 'email',
            'recipient' => 'learner@example.com',
            'subject' => 'Enrolment Confirmation',
            'body' => 'Your enrolment has been confirmed.',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
        ]);

        NotificationLog::create([
            'channel' => 'sms',
            'recipient' => '+60123456789',
            'subject' => 'Payment Received',
            'body' => 'Your payment of RM150.00 has been received.',
            'status' => 'sent',
            'sent_at' => now(),
            'created_at' => now(),
        ]);

        NotificationLog::create([
            'channel' => 'email',
            'recipient' => 'invalid@example.com',
            'subject' => 'Refund Processed',
            'body' => 'Your refund has been processed.',
            'status' => 'failed',
            'error_message' => 'SMTP connection timeout',
            'created_at' => now(),
        ]);
    }
}
