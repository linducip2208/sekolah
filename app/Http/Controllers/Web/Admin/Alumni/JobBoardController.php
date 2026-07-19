<?php

namespace App\Http\Controllers\Web\Admin\Alumni;

use App\Http\Controllers\Controller;
use App\Models\Alumni\AlumniProfile;
use App\Models\Alumni\JobApplication;
use App\Models\Alumni\JobListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobBoardController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();
        $query = JobListing::where('school_id', $schoolId)
            ->with(['alumniProfile.user:id,name', 'applications'])
            ->withCount('applications')
            ->orderByDesc('posted_at');

        if ($request->has('type') && $request->type !== '') {
            $query->where('job_type', $request->type);
        }
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->search . '%')
                  ->orWhere('position_title', 'like', '%' . $request->search . '%')
                  ->orWhere('location', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->has('verified') && $request->verified !== '') {
            $query->where('is_verified', $request->verified === '1');
        }

        $listings = $query->paginate(20)->appends($request->query());

        return view('school-admin.alumni.jobs.index', [
            'listings'    => $listings,
            'jobTypes'    => ['fulltime' => 'Full-time', 'parttime' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Kontrak', 'freelance' => 'Freelance'],
            'totalActive' => JobListing::where('school_id', $schoolId)->where('is_active', true)->count(),
            'totalToday'  => JobListing::where('school_id', $schoolId)->whereDate('posted_at', today())->count(),
        ]);
    }

    public function create(): View
    {
        $schoolId = $this->schoolId();
        $alumni = AlumniProfile::where('school_id', $schoolId)
            ->where('verified', true)
            ->with('user:id,name')
            ->orderBy('graduation_year', 'desc')
            ->get();

        return view('school-admin.alumni.jobs.create', [
            'alumni'   => $alumni,
            'jobTypes' => ['fulltime' => 'Full-time', 'parttime' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Kontrak', 'freelance' => 'Freelance'],
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'alumni_profile_id'  => 'required|exists:alumni_profiles,id',
            'company_name'       => 'required|string|max:200',
            'position_title'     => 'required|string|max:200',
            'job_type'           => 'required|in:fulltime,parttime,internship,contract,freelance',
            'location'           => 'nullable|string|max:200',
            'salary_range'       => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'requirements'       => 'nullable|string',
            'application_url'    => 'nullable|url|max:500',
            'application_email'  => 'nullable|email|max:200',
            'expires_at'         => 'nullable|date',
        ]);

        $alumniProfile = AlumniProfile::findOrFail($data['alumni_profile_id']);

        JobListing::create([
            'school_id'          => $this->schoolId(),
            'alumni_profile_id'  => $data['alumni_profile_id'],
            'company_name'       => $data['company_name'],
            'position_title'     => $data['position_title'],
            'job_type'           => $data['job_type'],
            'location'           => $data['location'] ?? null,
            'salary_range'       => $data['salary_range'] ?? null,
            'description'        => $data['description'] ?? null,
            'requirements'       => $data['requirements'] ?? null,
            'application_url'    => $data['application_url'] ?? null,
            'application_email'  => $data['application_email'] ?? null,
            'is_verified'        => false,
            'is_active'          => true,
            'posted_at'          => now(),
            'expires_at'         => $data['expires_at'] ?? null,
        ]);

        return redirect()->route('admin.jobs.index')->with('success', 'Lowongan kerja berhasil diposting.');
    }

    public function edit(JobListing $listing): View
    {
        $this->authorizeOwn($listing);
        $schoolId = $this->schoolId();
        $alumni = AlumniProfile::where('school_id', $schoolId)
            ->where('verified', true)
            ->with('user:id,name')
            ->orderBy('graduation_year', 'desc')
            ->get();

        return view('school-admin.alumni.jobs.create', [
            'listing'  => $listing,
            'alumni'   => $alumni,
            'jobTypes' => ['fulltime' => 'Full-time', 'parttime' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Kontrak', 'freelance' => 'Freelance'],
        ]);
    }

    public function update(Request $request, JobListing $listing): RedirectResponse
    {
        $this->authorizeOwn($listing);

        $data = $request->validate([
            'alumni_profile_id'  => 'required|exists:alumni_profiles,id',
            'company_name'       => 'required|string|max:200',
            'position_title'     => 'required|string|max:200',
            'job_type'           => 'required|in:fulltime,parttime,internship,contract,freelance',
            'location'           => 'nullable|string|max:200',
            'salary_range'       => 'nullable|string|max:100',
            'description'        => 'nullable|string',
            'requirements'       => 'nullable|string',
            'application_url'    => 'nullable|url|max:500',
            'application_email'  => 'nullable|email|max:200',
            'expires_at'         => 'nullable|date',
        ]);

        $listing->update($data);

        return redirect()->route('admin.jobs.index')->with('success', 'Lowongan kerja diperbarui.');
    }

    public function destroy(JobListing $listing): RedirectResponse
    {
        $this->authorizeOwn($listing);
        $listing->delete();
        return back()->with('success', 'Lowongan kerja dihapus.');
    }

    public function toggleVerify(JobListing $listing): RedirectResponse
    {
        $this->authorizeOwn($listing);
        $listing->update(['is_verified' => !$listing->is_verified]);
        return back()->with('success', 'Status verifikasi diubah.');
    }

    public function toggleActive(JobListing $listing): RedirectResponse
    {
        $this->authorizeOwn($listing);
        $listing->update(['is_active' => !$listing->is_active]);
        return back()->with('success', 'Status aktif diubah.');
    }

    public function applications(JobListing $listing, Request $request): View
    {
        $this->authorizeOwn($listing);

        $query = JobApplication::where('job_listing_id', $listing->id)
            ->orderByDesc('created_at');

        if ($request->has('status') && $request->status !== '') {
            $query->where('status', $request->status);
        }

        $applications = $query->paginate(20)->appends($request->query());
        $statuses = ['applied' => 'Melamar', 'reviewed' => 'Direview', 'interview' => 'Interview', 'offered' => 'Ditawarkan', 'rejected' => 'Ditolak', 'accepted' => 'Diterima'];

        $columns = [];
        foreach ($statuses as $key => $label) {
            $cols = JobApplication::where('job_listing_id', $listing->id)
                ->where('status', $key)
                ->orderByDesc('updated_at')
                ->get();
            $columns[$key] = ['label' => $label, 'items' => $cols, 'count' => $cols->count()];
        }

        return view('school-admin.alumni.jobs.applications', [
            'listing'      => $listing,
            'applications' => $applications,
            'columns'      => $columns,
            'statuses'     => $statuses,
            'selectedStatus' => $request->status,
        ]);
    }

    public function updateApplicationStatus(Request $request, JobApplication $application): RedirectResponse
    {
        $jobListing = $application->jobListing;
        $this->authorizeOwn($jobListing);

        $data = $request->validate([
            'status' => 'required|in:applied,reviewed,interview,offered,rejected,accepted',
            'notes'  => 'nullable|string',
        ]);

        $application->update([
            'status' => $data['status'],
            'notes'  => $data['notes'] ?? $application->notes,
        ]);

        return back()->with('success', 'Status pelamar diperbarui menjadi ' . $data['status'] . '.');
    }
}
