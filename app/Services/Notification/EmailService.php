<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Mail;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Log;

class EmailService
{
    public function send(string $to, string $subject, string $view, array $data = []): bool
    {
        try {
            Mail::send($view, $data, function (Message $msg) use ($to, $subject) {
                $msg->to($to)->subject($subject);
                $msg->from(config('mail.from.address'), config('mail.from.name'));
            });
            return true;
        } catch (\Throwable $e) {
            Log::warning('Email send failed', ['to' => $to, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendSubscriptionExpiry(string $email, string $schoolName, string $expiresAt, string $planName): bool
    {
        return $this->send($email, "Langganan {$schoolName} Akan Segera Berakhir", 'emails.subscription-expiry', [
            'schoolName' => $schoolName,
            'expiresAt'  => $expiresAt,
            'planName'   => $planName,
        ]);
    }

    public function sendFeeReminder(string $email, string $studentName, int $amountCents, string $dueDate): bool
    {
        return $this->send($email, "Pengingat Pembayaran SPP — {$studentName}", 'emails.fee-reminder', [
            'studentName' => $studentName,
            'amount'      => number_format($amountCents / 100, 0, ',', '.'),
            'dueDate'     => $dueDate,
        ]);
    }

    public function sendWelcomeSchool(string $email, string $schoolName, string $subdomain, string $adminPassword): bool
    {
        return $this->send($email, "Selamat Datang di Sikad Pro — {$schoolName}", 'emails.welcome-school', [
            'schoolName'   => $schoolName,
            'subdomain'    => $subdomain,
            'adminEmail'   => $email,
            'adminPassword' => $adminPassword,
        ]);
    }

    public function sendReportCard(string $email, string $studentName, string $semester): bool
    {
        return $this->send($email, "Rapor {$studentName} — {$semester}", 'emails.report-card', [
            'studentName' => $studentName,
            'semester'    => $semester,
        ]);
    }
}
