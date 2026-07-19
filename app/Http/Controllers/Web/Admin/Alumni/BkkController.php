<?php

namespace App\Http\Controllers\Web\Admin\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Alumni\BkkPartner;
use App\Models\Alumni\BkkPlacement;
use App\Models\Alumni\BkkReport;
use App\Models\Academic\AcademicYear;
use App\Models\User;
use App\Services\BkkService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BkkController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function dashboard(): View
    {
        $schoolId = $this->schoolId();
        $service = app(BkkService::class);

        return view('school-admin.alumni.bkk.dashboard', [
            'partnerStats' => $service->partnerStats($schoolId),
            'totalPlacements' => BkkPlacement::where('school_id', $schoolId)->count(),
            'activePlacements' => BkkPlacement::where('school_id', $schoolId)->where('status', 'active')->count(),
            'placementRate' => $service->placementPercentage($schoolId),
            'recentPlacements' => BkkPlacement::where('school_id', $schoolId)->with(['student', 'partner'])->latest()->take(5)->get(),
            'industryBreakdown' => $service->industryBreakdown($schoolId),
            'latestReport' => BkkReport::where('school_id', $schoolId)->latest()->first(),
        ]);
    }

    public function partners(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = BkkPartner::where('school_id', $schoolId)->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('mou_status', $request->status);
        }
        if ($request->filled('level')) {
            $query->where('partnership_level', $request->level);
        }

        return view('school-admin.alumni.bkk.partners', [
            'partners' => $query->paginate(20)->appends($request->query()),
            'mouStatuses' => ['draft' => 'Draf', 'signed' => 'Tertanda', 'active' => 'Aktif', 'expired' => 'Kadaluarsa'],
            'partnershipLevels' => ['gold' => 'Emas', 'silver' => 'Perak', 'bronze' => 'Perunggu'],
        ]);
    }

    public function storePartner(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'industry_type' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:200',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:200',
            'address' => 'nullable|string',
            'mou_status' => 'required|in:draft,signed,active,expired',
            'mou_start_date' => 'nullable|date',
            'mou_end_date' => 'nullable|date',
            'partnership_level' => 'required|in:gold,silver,bronze',
        ]);

        if ($request->hasFile('mou_file')) {
            $data['mou_file_path'] = $request->file('mou_file')->store('bkk/mou', 'public');
        }

        $data['school_id'] = $this->schoolId();
        BkkPartner::create($data);

        return redirect()->route('admin.bkk.partners')->with('success', 'Mitra BKK berhasil ditambahkan.');
    }

    public function updatePartner(Request $request, BkkPartner $partner): RedirectResponse
    {
        abort_unless($partner->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'company_name' => 'required|string|max:255',
            'industry_type' => 'nullable|string|max:100',
            'contact_person' => 'nullable|string|max:200',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:200',
            'address' => 'nullable|string',
            'mou_status' => 'required|in:draft,signed,active,expired',
            'mou_start_date' => 'nullable|date',
            'mou_end_date' => 'nullable|date',
            'partnership_level' => 'required|in:gold,silver,bronze',
        ]);

        if ($request->hasFile('mou_file')) {
            $data['mou_file_path'] = $request->file('mou_file')->store('bkk/mou', 'public');
        }

        $partner->update($data);
        return redirect()->route('admin.bkk.partners')->with('success', 'Mitra BKK berhasil diperbarui.');
    }

    public function deletePartner(BkkPartner $partner): RedirectResponse
    {
        abort_unless($partner->school_id === $this->schoolId(), 403);
        $partner->delete();
        return back()->with('success', 'Mitra BKK dihapus.');
    }

    public function placements(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = BkkPlacement::where('school_id', $schoolId)
            ->with(['student', 'partner'])
            ->orderByDesc('placement_date');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('contract')) {
            $query->where('contract_type', $request->contract);
        }

        return view('school-admin.alumni.bkk.placements', [
            'placements' => $query->paginate(20)->appends($request->query()),
            'students' => User::where('school_id', $schoolId)->orderBy('name')->get(),
            'partners' => BkkPartner::where('school_id', $schoolId)->orderBy('company_name')->get(),
            'contractTypes' => ['internship' => 'Magang', 'fulltime' => 'Full-time', 'contract' => 'Kontrak'],
            'statuses' => ['active' => 'Aktif', 'completed' => 'Selesai', 'terminated' => 'Terminasi'],
        ]);
    }

    public function storePlacement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
            'bkk_partner_id' => 'required|exists:bkk_partners,id',
            'position' => 'required|string|max:200',
            'placement_date' => 'required|date',
            'start_date' => 'nullable|date',
            'salary' => 'nullable|integer|min:0',
            'contract_type' => 'required|in:internship,fulltime,contract',
            'status' => 'required|in:active,completed,terminated',
            'supervisor_name' => 'nullable|string|max:200',
            'supervisor_phone' => 'nullable|string|max:50',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['salary'] = $data['salary'] ?? 0;
        BkkPlacement::create($data);

        return redirect()->route('admin.bkk.placements')->with('success', 'Penempatan berhasil dicatat.');
    }

    public function updatePlacement(Request $request, BkkPlacement $placement): RedirectResponse
    {
        abort_unless($placement->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'student_id' => 'required|exists:users,id',
            'bkk_partner_id' => 'required|exists:bkk_partners,id',
            'position' => 'required|string|max:200',
            'placement_date' => 'required|date',
            'start_date' => 'nullable|date',
            'salary' => 'nullable|integer|min:0',
            'contract_type' => 'required|in:internship,fulltime,contract',
            'status' => 'required|in:active,completed,terminated',
            'supervisor_name' => 'nullable|string|max:200',
            'supervisor_phone' => 'nullable|string|max:50',
        ]);

        $data['salary'] = $data['salary'] ?? 0;
        $placement->update($data);
        return redirect()->route('admin.bkk.placements')->with('success', 'Penempatan diperbarui.');
    }

    public function deletePlacement(BkkPlacement $placement): RedirectResponse
    {
        abort_unless($placement->school_id === $this->schoolId(), 403);
        $placement->delete();
        return back()->with('success', 'Penempatan dihapus.');
    }

    public function reports(Request $request): View
    {
        $schoolId = $this->schoolId();
        $reports = BkkReport::where('school_id', $schoolId)
            ->with('academicYear')
            ->orderByDesc('report_date')
            ->paginate(20);

        return view('school-admin.alumni.bkk.reports', [
            'reports' => $reports,
            'academicYears' => AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get(),
        ]);
    }

    public function generateReport(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_year_id' => 'required|exists:academic_years,id',
            'semester' => 'required|integer|in:1,2',
        ]);

        $service = app(BkkService::class);
        $service->generateReport($this->schoolId(), $data['academic_year_id'], (int) $data['semester']);

        return redirect()->route('admin.bkk.reports')->with('success', 'Laporan BKK berhasil digenerate.');
    }

    public function updateReport(Request $request, BkkReport $report): RedirectResponse
    {
        abort_unless($report->school_id === $this->schoolId(), 403);

        $data = $request->validate([
            'total_graduates' => 'required|integer|min:0',
            'total_placed' => 'required|integer|min:0',
            'total_entrepreneur' => 'required|integer|min:0',
            'total_university' => 'required|integer|min:0',
            'total_unemployed' => 'required|integer|min:0',
            'status' => 'required|in:draft,submitted,verified',
        ]);

        $report->update($data);
        return back()->with('success', 'Laporan diperbarui.');
    }

    public function deleteReport(BkkReport $report): RedirectResponse
    {
        abort_unless($report->school_id === $this->schoolId(), 403);
        $report->delete();
        return back()->with('success', 'Laporan dihapus.');
    }
}
