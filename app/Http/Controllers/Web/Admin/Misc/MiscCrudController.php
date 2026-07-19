<?php

namespace App\Http\Controllers\Web\Admin\Misc;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Inventory\Asset;
use App\Models\Inventory\MaintenanceRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class MiscCrudController extends Controller
{
    private function schoolId(): int { return auth()->user()->school_id; }
    private function authorizeOwn($model): void { abort_unless($model->school_id === $this->schoolId(), 403); }

    /* ============== MAINTENANCE REQUESTS ============== */

    public function maintenance(): View
    {
        $requests = MaintenanceRequest::where('school_id', $this->schoolId())
            ->with(['asset:id,name,asset_code'])
            ->orderByDesc('created_at')->paginate(25);
        $assets = Asset::where('school_id', $this->schoolId())->orderBy('name')->get(['id', 'name', 'asset_code']);
        return view('school-admin.misc.maintenance', compact('requests', 'assets'));
    }

    public function storeMaintenance(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'asset_id'          => 'nullable|exists:assets,id',
            'location_text'     => 'nullable|string|max:200',
            'issue_description' => 'required|string',
            'priority'          => 'required|in:low,medium,high,critical',
        ]);
        MaintenanceRequest::create([
            'school_id'         => $this->schoolId(),
            'asset_id'          => $data['asset_id'] ?? null,
            'location_text'     => $data['location_text'] ?? null,
            'reported_by'       => auth()->id(),
            'issue_description' => $data['issue_description'],
            'priority'          => $data['priority'],
            'status'            => 'open',
        ]);
        return back()->with('success', 'Laporan maintenance dicatat.');
    }

    public function resolveMaintenance(Request $request, MaintenanceRequest $req): RedirectResponse
    {
        $this->authorizeOwn($req);
        $data = $request->validate([
            'resolution_note'  => 'required|string',
            'cost_rupiah'      => 'nullable|numeric|min:0',
        ]);
        $req->update([
            'status'          => 'resolved',
            'resolution_note' => $data['resolution_note'],
            'cost'            => isset($data['cost_rupiah']) ? (int)($data['cost_rupiah']*100) : null,
            'resolved_at'     => now(),
        ]);
        return back()->with('success', 'Maintenance ditandai selesai.');
    }

    /* ============== CANTEEN WALLET / TOPUP ============== */

    public function canteenWallets(): View
    {
        $schoolId = $this->schoolId();
        $wallets = DB::table('canteen_wallets as cw')
            ->join('students as s', 'cw.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('cw.school_id', $schoolId)
            ->select('cw.*', 'u.name as student_name', 's.admission_no')
            ->orderBy('u.name')->paginate(30);
        return view('school-admin.misc.canteen-wallets', compact('wallets'));
    }

    public function topupWallet(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'    => 'required|exists:students,id',
            'amount_rupiah' => 'required|numeric|min:0',
        ]);

        $schoolId = $this->schoolId();
        DB::transaction(function () use ($data, $schoolId) {
            $walletId = DB::table('canteen_wallets')
                ->updateOrInsert(
                    ['school_id' => $schoolId, 'student_id' => $data['student_id']],
                    ['updated_at' => now(), 'created_at' => now()]
                );

            $wallet = DB::table('canteen_wallets')
                ->where('school_id', $schoolId)
                ->where('student_id', $data['student_id'])->first();

            $amount = (int)($data['amount_rupiah'] * 100);
            DB::table('canteen_wallets')->where('id', $wallet->id)->increment('balance', $amount);

            DB::table('canteen_topups')->insert([
                'school_id'         => $schoolId,
                'canteen_wallet_id' => $wallet->id,
                'initiated_by'      => auth()->id(),
                'amount'            => $amount,
                'status'            => 'completed',
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
        });

        return back()->with('success', 'Top-up dompet berhasil.');
    }

    /* ============== DAILY REPORTS ============== */

    public function dailyReports(): View
    {
        $reports = DB::table('daily_reports as dr')
            ->join('students as s', 'dr.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('dr.school_id', $this->schoolId())
            ->orderByDesc('dr.report_date')
            ->select('dr.*', 'u.name as student_name', 's.admission_no')
            ->paginate(40);
        return view('school-admin.misc.daily-reports', compact('reports'));
    }

    /* ============== CAREER ASSESSMENTS ============== */

    public function careerAssessments(): View
    {
        $assessments = DB::table('career_assessments as ca')
            ->join('students as s', 'ca.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('ca.school_id', $this->schoolId())
            ->orderByDesc('ca.taken_at')
            ->select('ca.*', 'u.name as student_name')
            ->paginate(30);
        return view('school-admin.misc.career-assessments', compact('assessments'));
    }

    /* ============== COLLEGE DATABASE (read-only public) ============== */

    public function collegeDatabase(): View
    {
        $colleges = DB::table('college_database')->orderBy('name')->paginate(30);
        return view('school-admin.misc.colleges', compact('colleges'));
    }

    /* ============== INTERNSHIP PLACEMENTS ============== */

    public function internships(): View
    {
        $internships = DB::table('internship_placements as ip')
            ->join('students as s', 'ip.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('ip.school_id', $this->schoolId())
            ->orderByDesc('ip.start_date')
            ->select('ip.*', 'u.name as student_name', 's.admission_no')
            ->paginate(30);
        $students = Student::where('school_id', $this->schoolId())->with('user:id,name')->get();
        return view('school-admin.misc.internships', compact('internships', 'students'));
    }

    public function storeInternship(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'   => 'required|exists:students,id',
            'company_name' => 'required|string|max:200',
            'position'     => 'nullable|string|max:200',
            'mentor_name'  => 'nullable|string|max:200',
            'mentor_phone' => 'nullable|string|max:30',
            'start_date'   => 'required|date',
            'end_date'     => 'required|date|after:start_date',
        ]);
        DB::table('internship_placements')->insert([
            'school_id'    => $this->schoolId(),
            'student_id'   => $data['student_id'],
            'company_name' => $data['company_name'],
            'position'     => $data['position'] ?? null,
            'mentor_name'  => $data['mentor_name'] ?? null,
            'mentor_phone' => $data['mentor_phone'] ?? null,
            'start_date'   => $data['start_date'],
            'end_date'     => $data['end_date'],
            'status'       => 'active',
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);
        return back()->with('success', 'Penempatan magang dicatat.');
    }

    /* ============== DIGITAL BADGES ============== */

    public function badges(): View
    {
        $badges = DB::table('digital_badges')->where('school_id', $this->schoolId())->orderBy('name')->get();
        return view('school-admin.misc.badges', compact('badges'));
    }

    public function storeBadge(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'description'    => 'nullable|string',
            'award_criteria' => 'nullable|string',
        ]);
        DB::table('digital_badges')->insert([
            'school_id'      => $this->schoolId(),
            'name'           => $data['name'],
            'description'    => $data['description'] ?? null,
            'award_criteria' => $data['award_criteria'] ?? null,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        return back()->with('success', 'Badge ditambahkan.');
    }

    /* ============== ALUMNI EVENTS / JOBS ============== */

    public function alumniEvents(): View
    {
        $events = DB::table('alumni_events')->where('school_id', $this->schoolId())
            ->orderByDesc('starts_at')->paginate(20);
        return view('school-admin.misc.alumni-events', compact('events'));
    }

    public function alumniJobs(): View
    {
        $jobs = DB::table('alumni_job_posts')->where('school_id', $this->schoolId())
            ->orderByDesc('created_at')->paginate(20);
        return view('school-admin.misc.alumni-jobs', compact('jobs'));
    }

    /* ============== KITAB KUNING & IBADAH (Religious) ============== */

    public function kitabKuning(): View
    {
        $progress = DB::table('kitab_kuning_progress as kk')
            ->join('students as s', 'kk.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('kk.school_id', $this->schoolId())
            ->orderByDesc('kk.updated_at')
            ->select('kk.*', 'u.name as student_name', 's.admission_no')
            ->paginate(30);
        return view('school-admin.misc.kitab-kuning', compact('progress'));
    }

    public function ibadahLog(): View
    {
        $logs = DB::table('ibadah_logs as il')
            ->join('students as s', 'il.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->where('il.school_id', $this->schoolId())
            ->orderByDesc('il.log_date')
            ->select('il.*', 'u.name as student_name', 's.admission_no')
            ->paginate(40);
        return view('school-admin.misc.ibadah-log', compact('logs'));
    }

    /* ============== CURRICULUM COMPETENCIES ============== */

    public function competencies(): View
    {
        $competencies = DB::table('curriculum_competencies as cc')
            ->leftJoin('curriculum_frameworks as cf', 'cc.curriculum_framework_id', '=', 'cf.id')
            ->where('cc.school_id', $this->schoolId())
            ->select('cc.*', 'cf.name as framework_name')
            ->orderBy('cc.code')->paginate(40);
        $frameworks = DB::table('curriculum_frameworks')->where('school_id', $this->schoolId())->get();
        return view('school-admin.misc.competencies', compact('competencies', 'frameworks'));
    }

    /* ============== LIVE CLASS ATTENDANCES ============== */

    public function liveClassAttendances(): View
    {
        $attendances = DB::table('live_class_attendances as la')
            ->join('students as s', 'la.student_id', '=', 's.id')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->join('live_class_sessions as lcs', 'la.live_class_session_id', '=', 'lcs.id')
            ->where('la.school_id', $this->schoolId())
            ->select('la.*', 'u.name as student_name', 'lcs.topic')
            ->orderByDesc('la.joined_at')->paginate(40);
        return view('school-admin.misc.live-class-attendances', compact('attendances'));
    }

    /* ============== PPDB ZONASI ZONES ============== */

    public function ppdbZones(): View
    {
        $zones = DB::table('ppdb_zonasi_zones')->where('school_id', $this->schoolId())->orderBy('district')->get();
        return view('school-admin.misc.ppdb-zones', compact('zones'));
    }

    public function storePpdbZone(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'district'       => 'required|string|max:200',
            'subdistrict'    => 'nullable|string|max:200',
            'priority_score' => 'required|numeric|min:0|max:1000',
        ]);
        DB::table('ppdb_zonasi_zones')->insert([
            'school_id'      => $this->schoolId(),
            'district'       => $data['district'],
            'subdistrict'    => $data['subdistrict'] ?? null,
            'priority_score' => $data['priority_score'],
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);
        return back()->with('success', 'Zona PPDB ditambahkan.');
    }
}
