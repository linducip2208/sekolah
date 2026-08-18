<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Mark;
use App\Models\Academic\ReportCard;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReportCardQrController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function generate(ReportCard $reportCard): RedirectResponse
    {
        abort_unless($reportCard->school_id === $this->schoolId(), 403);

        if (empty($reportCard->qr_token)) {
            $reportCard->update([
                'qr_token'          => bin2hex(random_bytes(32)),
                'verification_code' => strtoupper(bin2hex(random_bytes(16))),
            ]);
        }

        return back()->with('success', 'QR Token rapor berhasil di-generate.');
    }

    public function verifyPublic(string $token): View
    {
        $card = ReportCard::where('qr_token', $token)
            ->orWhere('verification_token', $token)
            ->firstOrFail();

        abort_unless($card->is_published, 404, 'Rapor belum dipublikasikan.');

        $marks = Mark::where('school_id', $card->school_id)
            ->where('student_id', $card->student_id)
            ->where('semester_id', $card->semester_id)
            ->with('subject:id,name')
            ->get();

        return view('public.verify-rapor', [
            'card'  => $card->load(['student.user', 'student.classSection.classRoom', 'student.classSection.section', 'semester']),
            'marks' => $marks,
            'token'=> $token,
        ]);
    }
}
