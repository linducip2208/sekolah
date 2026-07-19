<?php

namespace App\Services\LiveClass;

use App\Models\LiveClass\LiveClassSession;
use App\Models\LiveClass\VideoProvider;
use Illuminate\Support\Str;

class LiveClassService
{
    public function schedule(int $schoolId, int $teacherId, array $data): LiveClassSession
    {
        $provider = $data['video_provider_id']
            ? VideoProvider::where('school_id', $schoolId)->find($data['video_provider_id'])
            : VideoProvider::where('school_id', $schoolId)->where('is_active', true)->first();

        $meetingId = null;
        $joinUrl   = null;
        $hostUrl   = null;
        $passcode  = null;

        if ($provider) {
            ['meeting_id' => $meetingId, 'join_url' => $joinUrl, 'host_url' => $hostUrl, 'passcode' => $passcode]
                = $this->createMeeting($provider, $data);
        }

        return LiveClassSession::create([
            'school_id'         => $schoolId,
            'class_section_id'  => $data['class_section_id'],
            'subject_id'        => $data['subject_id'],
            'teacher_id'        => $teacherId,
            'video_provider_id' => $provider?->id,
            'topic'             => $data['topic'],
            'scheduled_start'   => $data['scheduled_start'],
            'duration_minutes'  => $data['duration_minutes'],
            'meeting_id'        => $meetingId,
            'join_url'          => $joinUrl,
            'host_url'          => $hostUrl,
            'passcode'          => $passcode,
            'status'            => 'scheduled',
        ]);
    }

    /**
     * Generic meeting creation. Format-driven, no vendor hardcode.
     * Returns array with meeting_id, join_url, host_url, passcode.
     */
    protected function createMeeting(VideoProvider $provider, array $data): array
    {
        // For self-hosted Jitsi/BBB: just generate a URL with random room
        if (in_array($provider->api_format, ['self_hosted_jitsi', 'self_hosted_bbb', 'manual_link'], true)) {
            $room = 'eschool-' . Str::lower(Str::random(12));
            return [
                'meeting_id' => $room,
                'join_url'   => rtrim($provider->base_url ?? '', '/') . '/' . $room,
                'host_url'   => rtrim($provider->base_url ?? '', '/') . '/' . $room . '?moderator=true',
                'passcode'   => null,
            ];
        }

        // For OAuth/REST APIs — adapter would call provider here. Stub returns generated room
        // until concrete adapter implemented. Avoids any vendor lock-in.
        $cfg          = (array) ($provider->extra_config ?? []);
        $room         = $cfg['room_prefix'] ?? 'class';
        $generatedId  = $room . '-' . Str::lower(Str::random(10));

        return [
            'meeting_id' => $generatedId,
            'join_url'   => rtrim($provider->base_url ?? '', '/') . '/j/' . $generatedId,
            'host_url'   => rtrim($provider->base_url ?? '', '/') . '/s/' . $generatedId,
            'passcode'   => Str::random(8),
        ];
    }

    public function start(LiveClassSession $session): LiveClassSession
    {
        $session->update(['status' => 'live']);
        return $session->fresh();
    }

    public function end(LiveClassSession $session, ?string $recordingUrl = null): LiveClassSession
    {
        $session->update([
            'status'        => 'ended',
            'recording_url' => $recordingUrl,
        ]);
        return $session->fresh();
    }
}
