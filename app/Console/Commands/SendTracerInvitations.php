<?php

namespace App\Console\Commands;

use App\Models\Alumni\AlumniProfile;
use App\Models\Alumni\TracerResponse;
use Illuminate\Console\Command;

class SendTracerInvitations extends Command
{
    protected $signature = 'tracer:send-invitations';
    protected $description = 'Kirim WhatsApp invitation ke alumni 1 tahun dan 3 tahun setelah lulus';

    public function handle(): int
    {
        $now = now();
        $targetYears = [$now->year - 1, $now->year - 3];

        $alumni = AlumniProfile::whereIn('graduation_year', $targetYears)
            ->with('user:id,name,email,school_id')
            ->get();

        $sent = 0;
        foreach ($alumni as $a) {
            $alreadyResponded = TracerResponse::where('alumni_profile_id', $a->id)->exists();
            if ($alreadyResponded) {
                continue;
            }

            $tracerUrl = route('alumni.tracer', ['alumni_id' => $a->id]);
            $message = "Halo {$a->user?->name}! Sekolah mengundang Anda untuk mengisi Tracer Study Alumni. Silakan isi di: {$tracerUrl} Terima kasih!";

            $this->info("Pesan untuk {$a->user?->email}: {$message}");

            // TODO: integrate with WhatsApp gateway when available
            $sent++;
        }

        $this->info("Total undangan terkirim: {$sent}");
        return Command::SUCCESS;
    }
}
