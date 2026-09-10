<?php

namespace App\Services\Visitor;

use App\Jobs\NotifyCanonicalVisitorHostJob;
use App\Models\User;
use App\Models\Visitor\Visitor;
use App\Models\Visitor\VisitorAuditLog;
use App\Models\Visitor\VisitorBadge;
use App\Models\Visitor\VisitorBlacklistEntry;
use App\Models\Visitor\VisitorVisit;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VisitorService
{
    public function register(int $schoolId, array $data, ?int $actorUserId = null, bool $preRegistered = false): VisitorVisit
    {
        return DB::transaction(function () use ($schoolId, $data, $actorUserId, $preRegistered) {
            if (! empty($data['host_user_id'])) {
                $hostBelongsToSchool = User::query()->whereKey($data['host_user_id'])
                    ->where('school_id', $schoolId)->exists();
                abort_unless($hostBelongsToSchool, 422, 'Host kunjungan tidak berasal dari sekolah yang dipilih.');
            }

            $visitor = $this->findOrCreateVisitor($schoolId, $data);
            if ($this->isBlacklisted($schoolId, $visitor, $data)) {
                throw new \RuntimeException('Pengunjung berada dalam daftar blacklist.');
            }

            $visit = VisitorVisit::withoutGlobalScopes()->create([
                'school_id' => $schoolId,
                'visitor_id' => $visitor->id,
                'visit_date' => $data['visit_date'] ?? now()->toDateString(),
                'purpose' => $data['purpose'],
                'host_user_id' => $data['host_user_id'] ?? null,
                'destination' => $data['destination'] ?? null,
                'expected_arrival' => $data['expected_arrival'] ?? null,
                'status' => $preRegistered ? 'pending' : 'checked_in',
                'check_in_at' => $preRegistered ? null : now(),
                'qr_token' => Str::random(64),
                'invitation_token' => $preRegistered ? Str::random(64) : null,
                'pre_registered' => $preRegistered,
                'notes' => $data['notes'] ?? null,
            ]);

            if (! $preRegistered) {
                $this->issueBadge($visit, $actorUserId);
            }
            $this->audit($visit, $actorUserId, $preRegistered ? 'registered' : 'checked_in', $data);
            if (! $preRegistered && $visit->host_user_id) {
                NotifyCanonicalVisitorHostJob::dispatch($visit->id)->afterCommit();
            }

            return $visit->load(['visitor', 'host', 'badges']);
        });
    }

    public function checkIn(int $schoolId, int $visitId, ?int $actorUserId = null): VisitorVisit
    {
        return DB::transaction(function () use ($schoolId, $visitId, $actorUserId) {
            $visit = VisitorVisit::withoutGlobalScopes()->where('school_id', $schoolId)->whereKey($visitId)->lockForUpdate()->firstOrFail();
            if (in_array($visit->status, ['checked_in', 'checked_out', 'cancelled'], true)) {
                throw new \RuntimeException('Status kunjungan tidak dapat check-in ulang.');
            }
            if ($this->isBlacklisted($schoolId, $visit->visitor, [])) {
                $visit->update(['status' => 'blocked']);
                $this->audit($visit, $actorUserId, 'blocked', ['reason' => 'blacklist']);
                throw new \RuntimeException('Pengunjung berada dalam daftar blacklist.');
            }
            $visit->update(['status' => 'checked_in', 'check_in_at' => now()]);
            $this->issueBadge($visit, $actorUserId);
            $this->audit($visit, $actorUserId, 'checked_in');
            if ($visit->host_user_id) {
                NotifyCanonicalVisitorHostJob::dispatch($visit->id)->afterCommit();
            }

            return $visit->fresh(['visitor', 'host', 'badges']);
        });
    }

    public function checkOut(int $schoolId, int $visitId, ?int $actorUserId = null): VisitorVisit
    {
        return DB::transaction(function () use ($schoolId, $visitId, $actorUserId) {
            $visit = VisitorVisit::withoutGlobalScopes()->where('school_id', $schoolId)->whereKey($visitId)->lockForUpdate()->firstOrFail();
            abort_if($visit->status !== 'checked_in' || $visit->check_out_at, 422, 'Kunjungan belum check-in atau sudah check-out.');
            $visit->update(['status' => 'checked_out', 'check_out_at' => now()]);
            $visit->badges()->whereNull('returned_at')->update(['returned_at' => now(), 'status' => 'returned']);
            $this->audit($visit, $actorUserId, 'checked_out');

            return $visit->fresh(['visitor', 'host', 'badges']);
        });
    }

    public function approve(int $schoolId, int $visitId, int $userId): VisitorVisit
    {
        $visit = VisitorVisit::withoutGlobalScopes()->where('school_id', $schoolId)->findOrFail($visitId);
        abort_unless($visit->status === 'pending', 422, 'Kunjungan tidak sedang menunggu persetujuan.');
        $visit->update(['status' => 'approved', 'approved_by' => $userId]);
        $this->audit($visit, $userId, 'approved');

        return $visit->fresh(['visitor', 'host', 'badges']);
    }

    public function issueBadge(VisitorVisit $visit, ?int $actorUserId = null): VisitorBadge
    {
        $existing = $visit->badges()->whereIn('status', ['issued', 'returned'])->latest('id')->first();
        if ($existing && $existing->status === 'issued') {
            return $existing;
        }

        $badge = VisitorBadge::withoutGlobalScopes()->create([
            'school_id' => $visit->school_id,
            'visitor_visit_id' => $visit->id,
            'badge_number' => 'V-'.$visit->school_id.'-'.str_pad((string) ($visit->id), 6, '0', STR_PAD_LEFT),
            'qr_token' => $visit->qr_token ?: Str::random(64),
            'issued_by' => $actorUserId,
            'issued_at' => now(),
            'status' => 'issued',
        ]);
        $visit->update(['badge_number' => $badge->badge_number, 'qr_token' => $badge->qr_token]);

        return $badge;
    }

    public function activeVisitors(int $schoolId)
    {
        return VisitorVisit::withoutGlobalScopes()->where('school_id', $schoolId)
            ->where('status', 'checked_in')->whereNull('check_out_at')
            ->with(['visitor', 'host', 'badges'])->latest('check_in_at')->get();
    }

    private function findOrCreateVisitor(int $schoolId, array $data): Visitor
    {
        $query = Visitor::withoutGlobalScopes()->where('school_id', $schoolId);
        if (! empty($data['identity_number'])) {
            $query->where('identity_type', $data['identity_type'] ?? null)->where('identity_number', $data['identity_number']);
        } elseif (! empty($data['phone'])) {
            $query->where('phone', $data['phone'])->where('name', $data['name']);
        } else {
            $query->where('name', $data['name'])->where('company', $data['company'] ?? null);
        }

        return $query->first() ?? Visitor::withoutGlobalScopes()->create([
            'school_id' => $schoolId,
            'name' => $data['name'],
            'identity_type' => $data['identity_type'] ?? null,
            'identity_number' => $data['identity_number'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'photo_path' => $data['photo_path'] ?? null,
            'company' => $data['company'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    private function isBlacklisted(int $schoolId, Visitor $visitor, array $data): bool
    {
        $new = VisitorBlacklistEntry::withoutGlobalScopes()->where('school_id', $schoolId)->where('is_active', true)
            ->where(function ($query) use ($visitor, $data) {
                $query->where('visitor_id', $visitor->id);
                if ($visitor->identity_number || ! empty($data['identity_number'])) {
                    $query->orWhere('identity_number', $visitor->identity_number ?? $data['identity_number']);
                }
            })->exists();
        $legacy = ! empty($visitor->identity_number) && DB::table('visitor_blacklist')
            ->where('school_id', $schoolId)->where('id_number', $visitor->identity_number)->exists();

        return $new || $legacy;
    }

    private function audit(VisitorVisit $visit, ?int $actorUserId, string $event, array $metadata = []): void
    {
        VisitorAuditLog::withoutGlobalScopes()->create([
            'school_id' => $visit->school_id,
            'visitor_id' => $visit->visitor_id,
            'visitor_visit_id' => $visit->id,
            'actor_user_id' => $actorUserId,
            'event' => $event,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'metadata' => $metadata,
            'created_at' => now(),
        ]);
    }
}
