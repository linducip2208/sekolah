<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\Medium;
use App\Models\Finance\FeeStructure;
use App\Services\Dashboard\SchoolSetupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SetupWizardController extends Controller
{
    public function index(SchoolSetupService $setup): View
    {
        $schoolId = (int) auth()->user()->school_id;
        $progress = $setup->progress($schoolId);

        return view('school-admin.setup.wizard', [
            'progress' => $progress,
            'school' => auth()->user()->school,
            'years' => AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->limit(5)->get(),
            'classes' => ClassRoom::where('school_id', $schoolId)->orderBy('name')->limit(20)->get(),
            'fees' => FeeStructure::where('school_id', $schoolId)->orderBy('name')->limit(20)->get(),
        ]);
    }

    public function saveProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:30',
            'address' => 'nullable|string|max:255',
        ]);
        $school = auth()->user()->school;
        $school->update($data);
        SchoolSetupService::flush((int) $school->id);

        return redirect()->route('admin.setup.wizard', ['step' => 'year'])->with('success', __('Setup profil disimpan.'));
    }

    public function saveYear(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        $schoolId = (int) auth()->user()->school_id;
        DB::transaction(function () use ($data, $schoolId) {
            AcademicYear::where('school_id', $schoolId)->update(['is_active' => false]);
            AcademicYear::create($data + ['school_id' => $schoolId, 'is_active' => true]);
        });
        SchoolSetupService::flush($schoolId);

        return redirect()->route('admin.setup.wizard', ['step' => 'class'])->with('success', __('Tahun ajaran diaktifkan.'));
    }

    public function saveClass(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
        ]);
        $schoolId = (int) auth()->user()->school_id;
        $medium = Medium::firstOrCreate(['school_id' => $schoolId, 'name' => 'Umum']);
        ClassRoom::create(['school_id' => $schoolId, 'medium_id' => $medium->id, 'name' => $data['name']]);
        SchoolSetupService::flush($schoolId);

        return redirect()->route('admin.setup.wizard', ['step' => 'students'])->with('success', __('Kelas ditambahkan.'));
    }

    public function saveFee(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'amount_rupiah' => 'required|numeric|min:0|max:1000000000',
        ]);
        $schoolId = (int) auth()->user()->school_id;
        FeeStructure::create([
            'school_id' => $schoolId,
            'name' => $data['name'],
            'amount' => (int) round(((float) $data['amount_rupiah']) * 100),
            'is_active' => true,
            'frequency' => 'monthly',
        ]);
        SchoolSetupService::flush($schoolId);

        return redirect()->route('admin.setup.wizard', ['step' => 'done'])->with('success', __('Struktur SPP disimpan.'));
    }
}
