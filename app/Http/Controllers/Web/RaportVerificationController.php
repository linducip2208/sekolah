<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Academic\Mark;
use App\Models\Academic\ReportCard;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\Response;
use Illuminate\View\View;

class RaportVerificationController extends Controller
{
    public function show(string $token): View
    {
        $card = ReportCard::where('verification_token', $token)->firstOrFail();

        abort_unless($card->is_published, 404, 'Rapor belum dipublikasikan.');

        $marks = Mark::where('school_id', $card->school_id)
            ->where('student_id', $card->student_id)
            ->where('semester_id', $card->semester_id)
            ->with('subject:id,name')
            ->get();

        return view('public.raport-verification', [
            'card'   => $card->load(['student.user', 'student.classSection.classRoom', 'student.classSection.section', 'semester']),
            'marks'  => $marks,
        ]);
    }

    public function qrcode(string $token): Response
    {
        $url = route('raport.verify', ['token' => $token]);

        $qrcode = new QRCode(new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale'           => 5,
            'margin'          => 2,
        ]));

        return response($qrcode->render($url), 200, ['Content-Type' => 'image/png']);
    }
}
