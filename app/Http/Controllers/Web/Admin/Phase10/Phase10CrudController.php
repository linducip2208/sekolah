<?php

namespace App\Http\Controllers\Web\Admin\Phase10;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Achievement\AchievementCategory;
use App\Models\Achievement\StudentAchievement;
use App\Models\Alumni\AlumniProfile;
use App\Models\Donation\Donation;
use App\Models\Donation\DonationCampaign;
use App\Models\Event\SchoolEvent;
use App\Models\Scholarship\ScholarshipApplication;
use App\Models\Scholarship\ScholarshipProgram;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class Phase10CrudController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    /* ============== DONATION CAMPAIGNS ============== */

    public function donationCampaigns(): View
    {
        return view('school-admin.donations.campaigns', [
            'campaigns' => DonationCampaign::where('school_id', $this->schoolId())
                ->withSum('donations as raised_actual', 'amount')
                ->orderByDesc('created_at')->paginate(20),
        ]);
    }

    public function storeDonationCampaign(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'         => 'required|string|max:200',
            'slug'          => 'required|alpha_dash|max:200',
            'description'   => 'required|string|max:2000',
            'target_rupiah' => 'required|numeric|min:0',
            'start_date'    => 'required|date',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'category'      => 'nullable|string|max:50',
        ]);
        DonationCampaign::create([
            'school_id'     => $this->schoolId(),
            'title'         => $data['title'],
            'slug'          => $data['slug'],
            'description'   => $data['description'],
            'target_amount' => (int)($data['target_rupiah'] * 100),
            'raised_amount' => 0,
            'start_date'    => $data['start_date'],
            'end_date'      => $data['end_date'],
            'category'      => $data['category'] ?? 'general',
            'status'        => 'active',
            'is_public'     => true,
        ]);
        return back()->with('success', 'Campaign donasi dibuat.');
    }

    public function deleteDonationCampaign(DonationCampaign $campaign): RedirectResponse
    {
        $this->authorizeOwn($campaign);
        $campaign->delete();
        return back()->with('success', 'Campaign dihapus.');
    }

    public function donationsList(): View
    {
        return view('school-admin.donations.list', [
            'donations' => Donation::where('school_id', $this->schoolId())
                ->with('campaign:id,title')->orderByDesc('created_at')->paginate(30),
        ]);
    }

    /* ============== ACHIEVEMENTS ============== */

    public function achievementCategories(): View
    {
        return view('school-admin.achievements.categories', [
            'categories' => AchievementCategory::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeAchievementCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'   => 'required|string|max:200',
            'scope'  => 'required|in:school,district,city,province,national,international',
            'points' => 'required|integer|min:0|max:1000',
        ]);
        $data['school_id'] = $this->schoolId();
        AchievementCategory::create($data);
        return back()->with('success', 'Kategori prestasi ditambahkan.');
    }

    public function deleteAchievementCategory(AchievementCategory $category): RedirectResponse
    {
        $this->authorizeOwn($category);
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }

    public function studentAchievements(): View
    {
        return view('school-admin.achievements.records', [
            'achievements' => StudentAchievement::where('school_id', $this->schoolId())
                ->with(['student.user:id,name', 'category'])
                ->orderByDesc('achieved_at')->paginate(25),
            'categories'   => AchievementCategory::where('school_id', $this->schoolId())->get(),
            'students'     => Student::where('school_id', $this->schoolId())->with('user:id,name')->get(),
        ]);
    }

    public function storeStudentAchievement(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'              => 'required|exists:students,id',
            'achievement_category_id' => 'required|exists:achievement_categories,id',
            'title'                   => 'required|string|max:255',
            'achieved_at'             => 'required|date',
            'issuer'                  => 'nullable|string|max:200',
            'description'             => 'nullable|string',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['verified']  = false;
        StudentAchievement::create($data);
        return back()->with('success', 'Prestasi tercatat.');
    }

    /* ============== SCHOLARSHIP ============== */

    public function scholarshipPrograms(): View
    {
        return view('school-admin.scholarship.programs', [
            'programs' => ScholarshipProgram::where('school_id', $this->schoolId())
                ->orderByDesc('open_date')->get(),
        ]);
    }

    public function storeScholarshipProgram(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'           => 'required|string|max:200',
            'source'         => 'required|in:school,foundation,government,corporate,individual',
            'discount_type'  => 'required|in:percentage,fixed,full',
            'discount_value' => 'required|numeric|min:0',
            'open_date'      => 'required|date',
            'close_date'     => 'required|date|after_or_equal:open_date',
            'quota'          => 'nullable|integer|min:1',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['discount_value'] = $data['discount_type'] === 'fixed'
            ? (int)($data['discount_value'] * 100)
            : (int)$data['discount_value'];
        $data['is_active'] = true;
        ScholarshipProgram::create($data);
        return back()->with('success', 'Program beasiswa dibuat.');
    }

    public function deleteScholarshipProgram(ScholarshipProgram $program): RedirectResponse
    {
        $this->authorizeOwn($program);
        $program->delete();
        return back()->with('success', 'Program dihapus.');
    }

    public function scholarshipApplications(): View
    {
        return view('school-admin.scholarship.applications', [
            'applications' => ScholarshipApplication::where('school_id', $this->schoolId())
                ->with(['student.user:id,name', 'program'])
                ->orderByDesc('created_at')->paginate(25),
        ]);
    }

    public function reviewScholarshipApplication(Request $request, ScholarshipApplication $application): RedirectResponse
    {
        $this->authorizeOwn($application);
        $request->validate(['status' => 'required|in:submitted,review,approved,rejected,active,completed']);
        $application->update([
            'status'        => $request->status,
            'reviewer_id'   => auth()->id(),
            'reviewer_note' => $request->reviewer_note,
        ]);
        return back()->with('success', 'Status aplikasi diperbarui.');
    }

    /* ============== EVENTS ============== */

    public function events(): View
    {
        return view('school-admin.events.list', [
            'events' => SchoolEvent::where('school_id', $this->schoolId())
                ->withCount('rsvps')
                ->orderByDesc('starts_at')->paginate(20),
        ]);
    }

    public function storeEvent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'              => 'required|string|max:255',
            'slug'               => 'required|alpha_dash|max:200',
            'description'        => 'required|string|max:5000',
            'event_type'         => 'required|in:academic,cultural,sports,fundraising,reunion,workshop,seminar,competition',
            'starts_at'          => 'required|date',
            'ends_at'            => 'required|date|after:starts_at',
            'venue'              => 'nullable|string|max:200',
            'city'               => 'nullable|string|max:100',
            'capacity'           => 'nullable|integer|min:1',
            'ticket_price_rupiah' => 'nullable|numeric|min:0',
            'target_audience'    => 'nullable|string|max:200',
        ]);
        SchoolEvent::create([
            'school_id'      => $this->schoolId(),
            'title'          => $data['title'],
            'slug'           => $data['slug'],
            'description'    => $data['description'],
            'event_type'     => $data['event_type'],
            'starts_at'      => $data['starts_at'],
            'ends_at'        => $data['ends_at'],
            'venue'          => $data['venue'] ?? null,
            'city'           => $data['city'] ?? null,
            'capacity'       => $data['capacity'] ?? null,
            'ticket_price'   => isset($data['ticket_price_rupiah']) ? (int)($data['ticket_price_rupiah']*100) : 0,
            'target_audience' => $data['target_audience'] ?? null,
        ]);
        return back()->with('success', 'Event dibuat.');
    }

    public function deleteEvent(SchoolEvent $event): RedirectResponse
    {
        $this->authorizeOwn($event);
        $event->delete();
        return back()->with('success', 'Event dihapus.');
    }

    /* ============== ALUMNI ============== */

    public function alumni(): View
    {
        return view('school-admin.alumni.list', [
            'alumni' => AlumniProfile::where('school_id', $this->schoolId())
                ->with('user:id,name,email')
                ->orderByDesc('graduation_year')->paginate(25),
        ]);
    }

    public function verifyAlumni(AlumniProfile $alumni): RedirectResponse
    {
        $this->authorizeOwn($alumni);
        $alumni->update(['verified' => !$alumni->verified]);
        return back()->with('success', 'Status verifikasi diubah.');
    }
}
