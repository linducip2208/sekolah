<?php

namespace App\Services;

use App\Models\Osis\OsisCandidate;
use App\Models\Osis\OsisElection;
use App\Models\Osis\OsisVote;
use Illuminate\Support\Facades\DB;

class OsisElectionService
{
    public function validateNominationPeriod(OsisElection $election): bool
    {
        if (!$election->nomination_start || !$election->nomination_end) {
            return false;
        }
        return now()->between($election->nomination_start, $election->nomination_end);
    }

    public function validateVotingPeriod(OsisElection $election): bool
    {
        if (!$election->voting_start || !$election->voting_end) {
            return false;
        }
        return now()->between($election->voting_start, $election->voting_end);
    }

    public function castVote(OsisElection $election, int $voterId, int $candidateId): array
    {
        if (!$this->validateVotingPeriod($election)) {
            return ['success' => false, 'error' => 'Periode voting tidak aktif.'];
        }

        $existing = OsisVote::where('osis_election_id', $election->id)
            ->where('voter_id', $voterId)
            ->exists();

        if ($existing) {
            return ['success' => false, 'error' => 'Anda sudah menggunakan hak suara Anda.'];
        }

        $candidate = OsisCandidate::find($candidateId);
        if (!$candidate || $candidate->osis_election_id !== $election->id) {
            return ['success' => false, 'error' => 'Kandidat tidak valid.'];
        }

        if ($candidate->status !== 'approved') {
            return ['success' => false, 'error' => 'Kandidat belum disetujui.'];
        }

        $vote = OsisVote::create([
            'osis_election_id' => $election->id,
            'voter_id'         => $voterId,
            'candidate_id'     => $candidateId,
            'voted_at'         => now(),
        ]);

        $candidate->increment('vote_count');

        return ['success' => true, 'vote' => $vote];
    }

    public function countVotes(OsisElection $election): array
    {
        return OsisCandidate::where('osis_election_id', $election->id)
            ->with('student.user')
            ->orderByDesc('vote_count')
            ->get()
            ->toArray();
    }

    public function generateWinnerList(OsisElection $election): array
    {
        $positions = $election->positions ?? [];
        $winners = [];

        foreach ($positions as $position) {
            $topCandidate = OsisCandidate::where('osis_election_id', $election->id)
                ->where('position', $position)
                ->where('status', 'approved')
                ->orderByDesc('vote_count')
                ->with('student.user')
                ->first();

            if ($topCandidate) {
                $winners[] = [
                    'position'    => $position,
                    'candidate'   => $topCandidate,
                    'vote_count'  => $topCandidate->vote_count,
                ];
            }
        }

        return $winners;
    }

    public function hasAlreadyVoted(OsisElection $election, int $voterId): bool
    {
        return OsisVote::where('osis_election_id', $election->id)
            ->where('voter_id', $voterId)
            ->exists();
    }

    public function getTotalVoters(OsisElection $election): int
    {
        return OsisVote::where('osis_election_id', $election->id)
            ->distinct('voter_id')
            ->count('voter_id');
    }
}
