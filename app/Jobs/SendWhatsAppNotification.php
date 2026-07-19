<?php

namespace App\Jobs;

use App\Services\Communication\WhatsAppNotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        private string $phone,
        private string $message,
        private ?int $schoolId = null,
    ) {}

    public function handle(WhatsAppNotificationService $service): void
    {
        Log::info('WhatsApp job processing', ['phone' => $this->maskPhone($this->phone)]);

        $result = $service->send($this->phone, $this->message, $this->schoolId);

        if (!$result['success']) {
            Log::warning('WhatsApp job failed', [
                'phone'  => $this->maskPhone($this->phone),
                'error'  => $result['error'] ?? 'Unknown',
            ]);
        }
    }

    private function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 6) return '***';
        return substr($phone, 0, 3) . str_repeat('*', $len - 6) . substr($phone, -3);
    }
}
