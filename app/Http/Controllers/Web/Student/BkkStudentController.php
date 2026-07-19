<?php

namespace App\Http\Controllers\Web\Student;

use App\Http\Controllers\Controller;
use App\Models\Alumni\BkkApplication;
use App\Models\Alumni\BkkPartner;
use App\Models\Alumni\JobListing;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BkkStudentController extends Controller
{
    public function index(Request $request): View
    {
        $userId = auth()->id();

        $partnerIds = BkkPartner::whereHas('placements', function ($q) {
            $q->where('school_id', auth()->user()->school_id);
        })->pluck('id');

        $query = JobListing::where('is_active', true)->where('is_verified', true)
            ->where(function ($q) use ($partnerIds) {
                $q->whereIn('school_id', function ($sub) use ($partnerIds) {
                    $sub->select('school_id')->from('bkk_partners')->whereIn('id', $partnerIds);
                });
            })
            ->orderByDesc('posted_at');

        if ($request->filled('type')) {
            $query->where('job_type', $request->type);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('company_name', 'like', '%' . $request->search . '%')
                  ->orWhere('position_title', 'like', '%' . $request->search . '%');
            });
        }

        $listings = $query->paginate(12);

        $myApplications = BkkApplication::where('student_id', $userId)
            ->pluck('status', 'job_listing_id')
            ->toArray();

        return view('student-portal.bkk.index', [
            'listings' => $listings,
            'myApplications' => $myApplications,
            'applicationHistory' => BkkApplication::where('student_id', $userId)
                ->with('jobListing')
                ->latest()
                ->take(10)
                ->get(),
        ]);
    }

    public function apply(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'job_listing_id' => 'required|exists:job_listings,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $jobListing = JobListing::findOrFail($data['job_listing_id']);
        $alreadyApplied = BkkApplication::where('student_id', auth()->id())
            ->where('job_listing_id', $data['job_listing_id'])
            ->exists();

        if ($alreadyApplied) {
            return back()->with('error', 'Anda sudah melamar lowongan ini.');
        }

        $partner = BkkPartner::where(function ($q) use ($jobListing) {
            $q->where('school_id', $jobListing->school_id);
        })->first();

        BkkApplication::create([
            'student_id' => auth()->id(),
            'job_listing_id' => $data['job_listing_id'],
            'bkk_partner_id' => $partner?->id,
            'application_date' => now(),
            'status' => 'applied',
            'notes' => $data['notes'] ?? null,
        ]);

        return back()->with('success', 'Lamaran berhasil dikirim.');
    }

    public function myApplications(): View
    {
        return view('student-portal.bkk.index', [
            'listings' => JobListing::where('is_active', true)->where('is_verified', true)->paginate(6),
            'myApplications' => [],
            'applicationHistory' => BkkApplication::where('student_id', auth()->id())
                ->with('jobListing')
                ->latest()
                ->get(),
        ]);
    }
}
