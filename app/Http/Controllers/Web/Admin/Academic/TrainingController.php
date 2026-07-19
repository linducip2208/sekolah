<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Staff;
use App\Models\Academic\TeacherCertification;
use App\Models\Academic\Training;
use App\Models\Academic\TrainingParticipant;
use App\Models\User;
use App\Services\TrainingService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TrainingController extends Controller
{
    private TrainingService $trainingService;

    public function __construct(TrainingService $trainingService)
    {
        $this->trainingService = $trainingService;
    }

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();
        $stats = $this->trainingService->getTrainingStats($schoolId);

        $trainings = Training::where('school_id', $schoolId)
            ->withCount('participants')
            ->orderByDesc('start_date')
            ->paginate(15);

        return view('school-admin.academic.training.index', compact('trainings', 'stats'));
    }

    public function create(): View
    {
        return view('school-admin.academic.training.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'provider'      => 'nullable|string|max:255',
            'training_type' => 'required|in:seminar,workshop,diklat,online,sertifikasi',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'duration_hours'=> 'nullable|integer|min:1|max:1000',
            'location'      => 'nullable|string|max:255',
            'certificate_template' => 'nullable|string',
            'is_mandatory'  => 'boolean',
            'description'   => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();

        Training::create($data);

        return redirect()->route('admin.training.index')
            ->with('success', 'Pelatihan "' . $data['title'] . '" berhasil dibuat.');
    }

    public function edit(Training $training): View
    {
        $this->authorizeOwn($training);
        return view('school-admin.academic.training.create', compact('training'));
    }

    public function update(Request $request, Training $training): RedirectResponse
    {
        $this->authorizeOwn($training);

        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'provider'      => 'nullable|string|max:255',
            'training_type' => 'required|in:seminar,workshop,diklat,online,sertifikasi',
            'start_date'    => 'required|date',
            'end_date'      => 'nullable|date|after_or_equal:start_date',
            'duration_hours'=> 'nullable|integer|min:1|max:1000',
            'location'      => 'nullable|string|max:255',
            'certificate_template' => 'nullable|string',
            'is_mandatory'  => 'boolean',
            'description'   => 'nullable|string',
        ]);

        $training->update($data);

        return redirect()->route('admin.training.index')
            ->with('success', 'Pelatihan diperbarui.');
    }

    public function destroy(Training $training): RedirectResponse
    {
        $this->authorizeOwn($training);
        $training->delete();
        return back()->with('success', 'Pelatihan dihapus.');
    }

    public function participants(Training $training): View
    {
        $this->authorizeOwn($training);
        $schoolId = $this->schoolId();

        $participants = $training->participants()->with('staff')->get();
        $staffList = User::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['teacher', 'admin']))
            ->orderBy('name')
            ->get(['id', 'name']);

        $completion = $this->trainingService->getTrainingCompletionRate($training->id);

        return view('school-admin.academic.training.participants', compact(
            'training', 'participants', 'staffList', 'completion'
        ));
    }

    public function registerParticipant(Request $request, Training $training): RedirectResponse
    {
        $this->authorizeOwn($training);

        $data = $request->validate([
            'staff_ids'   => 'required|array|min:1',
            'staff_ids.*' => 'exists:users,id',
        ]);

        foreach ($data['staff_ids'] as $staffId) {
            TrainingParticipant::firstOrCreate(
                ['training_id' => $training->id, 'staff_id' => $staffId],
                ['status' => 'registered']
            );
        }

        return back()->with('success', 'Peserta berhasil didaftarkan.');
    }

    public function updateParticipantStatus(Request $request, Training $training, TrainingParticipant $participant): RedirectResponse
    {
        $this->authorizeOwn($training);

        $data = $request->validate([
            'status' => 'required|in:registered,attended,completed,absent',
            'score'  => 'nullable|integer|min:0|max:100',
            'feedback' => 'nullable|string',
        ]);

        $participant->update($data);

        return back()->with('success', 'Status peserta diperbarui.');
    }

    public function removeParticipant(Training $training, TrainingParticipant $participant): RedirectResponse
    {
        $this->authorizeOwn($training);
        $participant->delete();
        return back()->with('success', 'Peserta dihapus dari pelatihan.');
    }

    public function issueCertificate(Request $request, Training $training, TrainingParticipant $participant): RedirectResponse
    {
        $this->authorizeOwn($training);

        $this->trainingService->issueCertificate($participant);

        return back()->with('success', 'Sertifikat diterbitkan. Nomor: ' . $participant->certificate_number);
    }

    public function certificatePdf(Training $training, TrainingParticipant $participant): \Illuminate\Http\Response
    {
        $this->authorizeOwn($training);
        $participant->load('staff', 'training');

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('pdf.training-certificate', [
            'participant' => $participant,
            'training'    => $training,
            'school'      => \App\Models\School::find($this->schoolId()),
        ]);

        return $pdf->download("sertifikat-{$participant->certificate_number}.pdf");
    }

    public function certifications(): View
    {
        $schoolId = $this->schoolId();

        $certifications = TeacherCertification::where('school_id', $schoolId)
            ->with('staff')
            ->orderByDesc('issue_date')
            ->paginate(20);

        $staffList = User::where('school_id', $schoolId)
            ->where('is_active', true)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['teacher', 'admin']))
            ->orderBy('name')
            ->get(['id', 'name']);

        return view('school-admin.academic.training.certifications', compact('certifications', 'staffList'));
    }

    public function storeCertification(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'staff_id'            => 'required|exists:users,id',
            'certification_name'  => 'required|string|max:255',
            'issuing_body'        => 'required|string|max:255',
            'certificate_number'  => 'required|string|max:100',
            'issue_date'          => 'required|date',
            'expiry_date'         => 'nullable|date|after:issue_date',
            'is_primary'          => 'boolean',
            'notes'               => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();

        TeacherCertification::create($data);

        return back()->with('success', 'Sertifikasi guru ditambahkan.');
    }

    public function updateCertification(Request $request, TeacherCertification $certification): RedirectResponse
    {
        $this->authorizeOwn($certification);

        $data = $request->validate([
            'certification_name'  => 'required|string|max:255',
            'issuing_body'        => 'required|string|max:255',
            'certificate_number'  => 'required|string|max:100',
            'issue_date'          => 'required|date',
            'expiry_date'         => 'nullable|date|after:issue_date',
            'is_primary'          => 'boolean',
            'notes'               => 'nullable|string',
        ]);

        $certification->update($data);

        return back()->with('success', 'Sertifikasi diperbarui.');
    }

    public function deleteCertification(TeacherCertification $certification): RedirectResponse
    {
        $this->authorizeOwn($certification);
        $certification->delete();
        return back()->with('success', 'Sertifikasi dihapus.');
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }
}
