<?php

namespace App\Services\Notification;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $apiKey;
    private string $sender;
    private string $endpoint = 'https://api.fonnte.com/send';

    public function __construct()
    {
        $this->apiKey = config('services.whatsapp.api_key', '');
        $this->sender = config('services.whatsapp.sender', '');
    }

    public function send(string $phone, string $message): bool
    {
        if (empty($this->apiKey)) {
            return false;
        }

        $phone = $this->normalizePhone($phone);

        try {
            $response = Http::withHeaders(['Authorization' => $this->apiKey])
                ->post($this->endpoint, [
                    'target'  => $phone,
                    'message' => $message,
                    'sender'  => $this->sender,
                ]);

            return $response->json('status', false) === true;
        } catch (\Throwable $e) {
            Log::warning('WhatsApp send failed', ['phone' => $phone, 'error' => $e->getMessage()]);
            return false;
        }
    }

    public function sendFeeReminder(string $phone, string $studentName, int $amountCents, string $dueDate): bool
    {
        $amount  = 'Rp ' . number_format($amountCents / 100, 0, ',', '.');
        $message = "Yth. Orang Tua/Wali Murid {$studentName},\n\n"
            . "Kami mengingatkan bahwa tagihan SPP sebesar *{$amount}* akan jatuh tempo pada *{$dueDate}*.\n\n"
            . "Mohon segera melakukan pembayaran. Terima kasih.\n\n_eSchool SaaS_";

        return $this->send($phone, $message);
    }

    public function sendAbsenceAlert(string $phone, string $studentName, string $date): bool
    {
        $message = "Yth. Orang Tua/Wali Murid {$studentName},\n\n"
            . "Kami informasikan bahwa *{$studentName}* tercatat *tidak hadir (alpha)* pada tanggal *{$date}*.\n\n"
            . "Jika ada keterangan, silakan hubungi wali kelas. Terima kasih.\n\n_eSchool SaaS_";

        return $this->send($phone, $message);
    }

    public function sendReportCardReady(string $phone, string $studentName, string $semester): bool
    {
        $message = "Yth. Orang Tua/Wali Murid *{$studentName}*,\n\n"
            . "Rapor semester *{$semester}* telah tersedia. Silakan login ke aplikasi untuk melihat nilai.\n\n_eSchool SaaS_";

        return $this->send($phone, $message);
    }

    private function normalizePhone(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            $phone = '62' . substr($phone, 1);
        }
        return $phone;
    }
}
