<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\School;
use App\Services\Visitor\VisitorService;
use chillerlan\QRCode\Output\QRGdImagePNG;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VisitorRegistrationController extends Controller
{
    public function __construct(private readonly VisitorService $service) {}

    public function showForm(): View
    {
        $schoolId = $this->schoolId(request());
        abort_unless($schoolId, 404, 'Sekolah tujuan tidak ditemukan. Gunakan tautan pendaftaran dari sekolah Anda.');

        $staff = Staff::withoutGlobalScopes()->where('school_id', $schoolId)
            ->with('user:id,name')->orderBy('id')->get();

        return view('visitor.register', compact('staff'));
    }

    public function submit(Request $request): View|RedirectResponse
    {
        $validated = $request->validate([
            'visitor_name' => 'required|string|max:200',
            'phone' => 'required|string|max:30',
            'purpose' => 'required|string|max:200',
            'host_staff_id' => 'required|integer|exists:staff,id',
            'expected_arrival' => 'required|date',
            'vehicle_plate' => 'nullable|string|max:20',
        ]);

        $hostStaff = Staff::withoutGlobalScopes()->with('user')->findOrFail($validated['host_staff_id']);
        $schoolId = $this->schoolId($request);
        abort_unless($schoolId && (int) $hostStaff->school_id === (int) $schoolId, 422, 'Sekolah tujuan tidak valid.');

        try {
            $visit = $this->service->register((int) $schoolId, [
                'name' => $validated['visitor_name'],
                'phone' => $validated['phone'],
                'purpose' => $validated['purpose'],
                'host_user_id' => $hostStaff->user_id,
                'expected_arrival' => $validated['expected_arrival'],
                'notes' => $validated['vehicle_plate'] ? 'Plat kendaraan: '.$validated['vehicle_plate'] : null,
            ], null, true);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['visitor_name' => $e->getMessage()])->withInput();
        }

        return view('visitor.register-success', [
            'visit' => $visit,
            'qrDataUrl' => $this->generateQrDataUri((string) $visit->qr_token),
        ]);
    }

    private function generateQrDataUri(string $token): string
    {
        $qrcode = new QRCode(new QROptions([
            'outputInterface' => QRGdImagePNG::class,
            'scale' => 5,
            'margin' => 2,
        ]));

        return 'data:image/png;base64,'.base64_encode($qrcode->render($token));
    }

    private function schoolId(Request $request): ?int
    {
        if (app()->bound('current_school') && app('current_school')) {
            return (int) app('current_school')->id;
        }

        $slug = $request->query('school', $request->input('school'));
        if (! filled($slug)) {
            return null;
        }

        return School::query()->where('subdomain', (string) $slug)
            ->where('is_active', true)->value('id');
    }
}
