<?php

namespace App\Http\Controllers\Web\Admin\Phase8;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\Student;
use App\Models\Counseling\BullyingReport;
use App\Models\Counseling\CounselingSession;
use App\Models\Discipline\DisciplineCategory;
use App\Models\Discipline\DisciplineRecord;
use App\Models\Facilities\TransportRoute;
use App\Models\Facilities\Vehicle;
use App\Models\Medical\ClinicVisit;
use App\Models\Medical\MedicalRecord;
use App\Models\Medical\Vaccination;
use App\Models\PPDB\PpdbApplication;
use App\Models\PPDB\PpdbPeriod;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class Phase8CrudController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    /* ============================== PPDB ============================== */

    public function ppdbPeriods(): View
    {
        return view('school-admin.ppdb.periods', [
            'periods' => PpdbPeriod::where('school_id', $this->schoolId())->orderByDesc('open_date')->get(),
            'years'   => AcademicYear::where('school_id', $this->schoolId())->orderByDesc('start_date')->get(),
        ]);
    }

    public function storePpdbPeriod(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'             => 'required|string|max:200',
            'academic_year_id' => 'required|exists:academic_years,id',
            'open_date'        => 'required|date',
            'close_date'       => 'required|date|after_or_equal:open_date',
            'announcement_date' => 'nullable|date',
            'reregistration_deadline' => 'nullable|date',
            'form_fee_rupiah'  => 'nullable|numeric|min:0',
        ]);
        PpdbPeriod::create([
            'school_id'              => $this->schoolId(),
            'academic_year_id'       => $data['academic_year_id'],
            'name'                   => $data['name'],
            'open_date'              => $data['open_date'],
            'close_date'             => $data['close_date'],
            'announcement_date'      => $data['announcement_date'] ?? null,
            'reregistration_deadline' => $data['reregistration_deadline'] ?? null,
            'form_fee'               => isset($data['form_fee_rupiah']) ? (int)($data['form_fee_rupiah']*100) : 0,
            'is_published'           => false,
        ]);
        return back()->with('success', 'Periode PPDB ditambahkan.');
    }

    public function publishPpdbPeriod(PpdbPeriod $period): RedirectResponse
    {
        $this->authorizeOwn($period);
        $period->update(['is_published' => !$period->is_published]);
        return back()->with('success', 'Status publish diubah.');
    }

    public function deletePpdbPeriod(PpdbPeriod $period): RedirectResponse
    {
        $this->authorizeOwn($period);
        $period->delete();
        return back()->with('success', 'Periode dihapus.');
    }

    public function ppdbApplications(Request $request): View
    {
        $applications = PpdbApplication::where('school_id', $this->schoolId())
            ->with('period:id,name')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->period_id, fn($q) => $q->where('ppdb_period_id', $request->period_id))
            ->orderByDesc('created_at')->paginate(25)->withQueryString();

        return view('school-admin.ppdb.applications', [
            'applications' => $applications,
            'periods'      => PpdbPeriod::where('school_id', $this->schoolId())->get(),
        ]);
    }

    public function reviewPpdbApplication(Request $request, PpdbApplication $application): RedirectResponse
    {
        $this->authorizeOwn($application);
        $request->validate(['status' => 'required|in:submitted,review,accepted,waitlist,rejected,enrolled']);
        $application->update(['status' => $request->status]);
        return back()->with('success', 'Status pendaftaran diperbarui.');
    }

    /* ============================== UKS / CLINIC ============================== */

    public function clinicVisits(Request $request): View
    {
        $visits = ClinicVisit::where('school_id', $this->schoolId())
            ->with(['student.user:id,name'])
            ->orderByDesc('visit_at')->paginate(25);

        return view('school-admin.clinic.visits', [
            'visits' => $visits,
            'students' => Student::where('school_id', $this->schoolId())->with('user:id,name')->get(),
        ]);
    }

    public function storeClinicVisit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'    => 'required|exists:students,id',
            'visit_at'      => 'required|date',
            'symptoms'      => 'required|string',
            'diagnosis'     => 'nullable|string',
            'treatment'     => 'nullable|string',
            'temperature_c' => 'nullable|numeric|min:30|max:45',
            'blood_pressure' => 'nullable|string|max:10',
            'returned_to_class' => 'nullable|boolean',
            'sent_home'     => 'nullable|boolean',
            'parent_notified' => 'nullable|boolean',
        ]);

        ClinicVisit::create(array_merge($data, [
            'school_id'        => $this->schoolId(),
            'attended_by'      => auth()->id(),
            'returned_to_class' => (bool)($data['returned_to_class'] ?? false),
            'sent_home'        => (bool)($data['sent_home'] ?? false),
            'parent_notified'  => (bool)($data['parent_notified'] ?? false),
            'referred_external' => false,
        ]));

        return back()->with('success', 'Kunjungan klinik tercatat.');
    }

    public function vaccinations(Request $request): View
    {
        $vaccinations = Vaccination::where('school_id', $this->schoolId())
            ->with('student.user:id,name')
            ->orderByDesc('vaccinated_at')->paginate(25);

        return view('school-admin.clinic.vaccinations', [
            'vaccinations' => $vaccinations,
            'students'     => Student::where('school_id', $this->schoolId())->with('user:id,name')->get(),
        ]);
    }

    public function storeVaccination(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'vaccine_name'    => 'required|string|max:200',
            'vaccinated_at'   => 'required|date',
            'batch_number'    => 'nullable|string|max:50',
            'administered_by' => 'nullable|string|max:200',
            'next_dose_due'   => 'nullable|date',
        ]);
        $data['school_id'] = $this->schoolId();
        Vaccination::create($data);
        return back()->with('success', 'Catatan vaksinasi tersimpan.');
    }

    /* ============================== COUNSELING (BP/BK) ============================== */

    public function counselingSessions(): View
    {
        return view('school-admin.counseling.sessions', [
            'sessions' => CounselingSession::where('school_id', $this->schoolId())
                ->with(['student.user:id,name', 'counselor:id,name'])
                ->orderByDesc('scheduled_at')->paginate(25),
            'students' => Student::where('school_id', $this->schoolId())->with('user:id,name')->get(),
            'counselors' => User::where('school_id', $this->schoolId())
                ->whereHas('roles', fn($q) => $q->whereIn('name', ['counselor', 'teacher', 'admin']))
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeCounselingSession(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'   => 'required|exists:students,id',
            'counselor_id' => 'required|exists:users,id',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'nullable|integer|min:5|max:300',
            'type'         => 'required|in:academic,behavior,mental_health,career,family,social',
            'notes'        => 'nullable|string',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['status'] = 'scheduled';
        CounselingSession::create($data);
        return back()->with('success', 'Sesi konseling dijadwalkan.');
    }

    public function bullyingReports(): View
    {
        return view('school-admin.counseling.bullying', [
            'reports' => BullyingReport::where('school_id', $this->schoolId())
                ->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function updateBullyingReport(Request $request, BullyingReport $report): RedirectResponse
    {
        $this->authorizeOwn($report);
        $data = $request->validate([
            'status'          => 'required|in:received,investigating,action_taken,closed,unfounded',
            'investigation_notes' => 'nullable|string',
            'action_summary'  => 'nullable|string',
        ]);
        $report->update($data);
        return back()->with('success', 'Laporan diperbarui.');
    }

    /* ============================== DISCIPLINE ============================== */

    public function disciplineCategories(): View
    {
        return view('school-admin.discipline.categories', [
            'categories' => DisciplineCategory::where('school_id', $this->schoolId())->orderBy('type')->orderBy('name')->get(),
        ]);
    }

    public function storeDisciplineCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:200',
            'type'        => 'required|in:violation,achievement',
            'point_value' => 'required|integer',
            'description' => 'nullable|string',
        ]);
        $data['school_id'] = $this->schoolId();
        DisciplineCategory::create($data);
        return back()->with('success', 'Kategori ditambahkan.');
    }

    public function deleteDisciplineCategory(DisciplineCategory $category): RedirectResponse
    {
        $this->authorizeOwn($category);
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    public function disciplineRecords(): View
    {
        return view('school-admin.discipline.records', [
            'records' => DisciplineRecord::where('school_id', $this->schoolId())
                ->with(['student.user:id,name', 'category', 'reporter:id,name'])
                ->orderByDesc('incident_date')->paginate(25),
            'categories' => DisciplineCategory::where('school_id', $this->schoolId())->get(),
            'students'   => Student::where('school_id', $this->schoolId())->with('user:id,name')->get(),
        ]);
    }

    public function storeDisciplineRecord(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'             => 'required|exists:students,id',
            'discipline_category_id' => 'required|exists:discipline_categories,id',
            'incident_date'          => 'required|date',
            'description'            => 'required|string',
            'sanction_applied'       => 'nullable|string',
        ]);
        $cat = DisciplineCategory::findOrFail($data['discipline_category_id']);
        DisciplineRecord::create([
            'school_id'              => $this->schoolId(),
            'student_id'             => $data['student_id'],
            'discipline_category_id' => $cat->id,
            'reported_by'            => auth()->id(),
            'incident_date'          => $data['incident_date'],
            'description'            => $data['description'],
            'points'                 => $cat->point_value,
            'status'                 => 'reported',
            'sanction_applied'       => $data['sanction_applied'] ?? null,
            'parent_notified'        => false,
        ]);
        return back()->with('success', 'Catatan disiplin ditambahkan.');
    }

    /* ============================== TRANSPORT ============================== */

    public function vehicles(): View
    {
        return view('school-admin.transport.vehicles', [
            'vehicles' => Vehicle::where('school_id', $this->schoolId())->orderBy('registration_no')->get(),
        ]);
    }

    public function storeVehicle(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'registration_no' => 'required|string|max:30',
            'make_model'      => 'nullable|string|max:200',
            'capacity'        => 'required|integer|min:1|max:100',
            'driver_name'     => 'nullable|string|max:200',
            'driver_phone'    => 'nullable|string|max:30',
        ]);
        $data['school_id'] = $this->schoolId();
        Vehicle::create($data);
        return back()->with('success', 'Kendaraan ditambahkan.');
    }

    public function deleteVehicle(Vehicle $vehicle): RedirectResponse
    {
        $this->authorizeOwn($vehicle);
        $vehicle->delete();
        return back()->with('success', 'Kendaraan dihapus.');
    }

    public function transportRoutes(): View
    {
        return view('school-admin.transport.routes', [
            'routes' => TransportRoute::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeTransportRoute(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:200',
            'fee_per_month_rupiah' => 'required|numeric|min:0',
        ]);
        TransportRoute::create([
            'school_id'     => $this->schoolId(),
            'name'          => $data['name'],
            'fee_per_month' => (int)($data['fee_per_month_rupiah'] * 100),
            'is_active'     => true,
        ]);
        return back()->with('success', 'Rute ditambahkan.');
    }

    public function deleteTransportRoute(TransportRoute $route): RedirectResponse
    {
        $this->authorizeOwn($route);
        $route->delete();
        return back()->with('success', 'Rute dihapus.');
    }
}
