<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Alumni\JobApplication;
use App\Models\Alumni\JobListing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AlumniJobController extends Controller
{
    public function index(Request $request): View
    {
        $query = JobListing::where('is_active', true)
            ->where('is_verified', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->with('alumniProfile.user:id,name')
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

        $listings = $query->paginate(15)->appends($request->query());
        $jobTypes = ['fulltime' => 'Full-time', 'parttime' => 'Part-time', 'internship' => 'Internship', 'contract' => 'Kontrak', 'freelance' => 'Freelance'];

        return view('job-board.index', [
            'listings' => $listings,
            'jobTypes' => $jobTypes,
        ]);
    }

    public function show(string $slug): View
    {
        $id = (int) \Illuminate\Support\Str::afterLast($slug, '-');

        $listing = JobListing::where('id', $id)
            ->where('is_active', true)
            ->where('is_verified', true)
            ->with(['alumniProfile.user:id,name', 'alumniProfile:id,graduation_year,current_position,current_company'])
            ->withCount('applications')
            ->firstOrFail();

        $listing->increment('view_count');

        return view('job-board.show', [
            'listing' => $listing,
        ]);
    }

    public function apply(Request $request, string $slug): RedirectResponse
    {
        $id = (int) \Illuminate\Support\Str::afterLast($slug, '-');

        $listing = JobListing::where('id', $id)
            ->where('is_active', true)
            ->firstOrFail();

        $data = $request->validate([
            'full_name'    => 'required|string|max:200',
            'email'        => 'required|email|max:200',
            'phone'        => 'nullable|string|max:30',
            'cover_letter' => 'nullable|string|max:5000',
            'resume'       => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $resumePath = null;
        if ($request->hasFile('resume')) {
            $resumePath = $request->file('resume')->store('job-applications/resumes', 'public');
        }

        $applicantType = 'public';
        $applicantId = null;
        if (auth()->check()) {
            $applicantType = 'student';
            $applicantId = auth()->id();
            $alumniProfile = \App\Models\Alumni\AlumniProfile::where('user_id', auth()->id())->first();
            if ($alumniProfile) {
                $applicantType = 'alumni';
            }
        }

        JobApplication::create([
            'job_listing_id' => $listing->id,
            'applicant_type' => $applicantType,
            'applicant_id'   => $applicantId,
            'full_name'      => $data['full_name'],
            'email'          => $data['email'],
            'phone'          => $data['phone'] ?? null,
            'cover_letter'   => $data['cover_letter'] ?? null,
            'resume_path'    => $resumePath,
            'status'         => 'applied',
        ]);

        return back()->with('success', 'Lamaran Anda berhasil dikirim untuk posisi ' . $listing->position_title . ' di ' . $listing->company_name . '.');
    }
}
