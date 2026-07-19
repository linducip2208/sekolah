<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassRoom;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Medium;
use App\Models\Academic\Section;
use App\Models\Academic\Subject;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AcademicWebController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    /* =================== ACADEMIC YEARS =================== */

    public function years(): View
    {
        return view('school-admin.academic.years', [
            'years' => AcademicYear::where('school_id', $this->schoolId())
                ->orderByDesc('start_date')->get(),
        ]);
    }

    public function storeYear(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'       => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['is_active'] = false;
        AcademicYear::create($data);
        return back()->with('success', 'Tahun ajaran ditambahkan.');
    }

    public function updateYear(Request $request, AcademicYear $year): RedirectResponse
    {
        $this->authorizeOwn($year);
        $data = $request->validate([
            'name'       => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after:start_date',
        ]);
        $year->update($data);
        return back()->with('success', 'Tahun ajaran diperbarui.');
    }

    public function activateYear(AcademicYear $year): RedirectResponse
    {
        $this->authorizeOwn($year);
        DB::transaction(function () use ($year) {
            AcademicYear::where('school_id', $this->schoolId())->update(['is_active' => false]);
            $year->update(['is_active' => true]);
        });
        return back()->with('success', "Tahun ajaran '{$year->name}' diaktifkan.");
    }

    public function deleteYear(AcademicYear $year): RedirectResponse
    {
        $this->authorizeOwn($year);
        $year->delete();
        return back()->with('success', 'Tahun ajaran dihapus.');
    }

    /* =================== SUBJECTS =================== */

    public function subjects(): View
    {
        return view('school-admin.academic.subjects', [
            'subjects' => Subject::where('school_id', $this->schoolId())->with('medium')->orderBy('name')->get(),
            'mediums'  => Medium::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeSubject(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'medium_id'    => 'nullable|exists:mediums,id',
            'name'         => 'required|string|max:100',
            'code'         => 'nullable|string|max:20',
            'type'         => 'nullable|string|in:theory,practical,both',
            'credit_hours' => 'nullable|integer|min:0|max:20',
        ]);
        $data['school_id'] = $this->schoolId();
        $data['is_active'] = true;
        Subject::create($data);
        return back()->with('success', 'Mata pelajaran ditambahkan.');
    }

    public function updateSubject(Request $request, Subject $subject): RedirectResponse
    {
        $this->authorizeOwn($subject);
        $data = $request->validate([
            'medium_id'    => 'nullable|exists:mediums,id',
            'name'         => 'required|string|max:100',
            'code'         => 'nullable|string|max:20',
            'type'         => 'nullable|string|in:theory,practical,both',
            'credit_hours' => 'nullable|integer|min:0|max:20',
            'is_active'    => 'nullable|boolean',
        ]);
        $data['is_active'] = (bool) ($data['is_active'] ?? false);
        $subject->update($data);
        return back()->with('success', 'Mata pelajaran diperbarui.');
    }

    public function deleteSubject(Subject $subject): RedirectResponse
    {
        $this->authorizeOwn($subject);
        $subject->delete();
        return back()->with('success', 'Mata pelajaran dihapus.');
    }

    /* =================== CLASS ROOMS =================== */

    public function classes(): View
    {
        return view('school-admin.academic.classes', [
            'classes' => ClassRoom::where('school_id', $this->schoolId())->with('medium')->orderBy('name')->get(),
            'mediums' => Medium::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeClass(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'medium_id' => 'required|exists:mediums,id',
            'name'      => 'required|string|max:100',
        ]);
        $data['school_id'] = $this->schoolId();
        ClassRoom::create($data);
        return back()->with('success', 'Kelas ditambahkan.');
    }

    public function updateClass(Request $request, ClassRoom $class): RedirectResponse
    {
        $this->authorizeOwn($class);
        $data = $request->validate([
            'medium_id' => 'required|exists:mediums,id',
            'name'      => 'required|string|max:100',
        ]);
        $class->update($data);
        return back()->with('success', 'Kelas diperbarui.');
    }

    public function deleteClass(ClassRoom $class): RedirectResponse
    {
        $this->authorizeOwn($class);
        $class->delete();
        return back()->with('success', 'Kelas dihapus.');
    }

    /* =================== SECTIONS =================== */

    public function sections(): View
    {
        return view('school-admin.academic.sections', [
            'sections' => Section::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeSection(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:50']);
        $data['school_id'] = $this->schoolId();
        Section::create($data);
        return back()->with('success', 'Section ditambahkan.');
    }

    public function updateSection(Request $request, Section $section): RedirectResponse
    {
        $this->authorizeOwn($section);
        $data = $request->validate(['name' => 'required|string|max:50']);
        $section->update($data);
        return back()->with('success', 'Section diperbarui.');
    }

    public function deleteSection(Section $section): RedirectResponse
    {
        $this->authorizeOwn($section);
        $section->delete();
        return back()->with('success', 'Section dihapus.');
    }

    /* =================== MEDIUMS =================== */

    public function mediums(): View
    {
        return view('school-admin.academic.mediums', [
            'mediums' => Medium::where('school_id', $this->schoolId())->orderBy('name')->get(),
        ]);
    }

    public function storeMedium(Request $request): RedirectResponse
    {
        $data = $request->validate(['name' => 'required|string|max:100']);
        $data['school_id'] = $this->schoolId();
        Medium::create($data);
        return back()->with('success', 'Medium ditambahkan.');
    }

    public function deleteMedium(Medium $medium): RedirectResponse
    {
        $this->authorizeOwn($medium);
        $medium->delete();
        return back()->with('success', 'Medium dihapus.');
    }

    /* =================== CLASS SECTIONS (combined) =================== */

    public function classSections(): View
    {
        $schoolId = $this->schoolId();
        return view('school-admin.academic.class-sections', [
            'classSections' => ClassSection::where('school_id', $schoolId)
                ->with(['classRoom', 'section', 'medium', 'academicYear', 'classTeacher'])
                ->withCount('students')
                ->orderBy('class_room_id')->orderBy('section_id')->get(),
            'classes'  => ClassRoom::where('school_id', $schoolId)->orderBy('name')->get(),
            'sections' => Section::where('school_id', $schoolId)->orderBy('name')->get(),
            'mediums'  => Medium::where('school_id', $schoolId)->orderBy('name')->get(),
            'years'    => AcademicYear::where('school_id', $schoolId)->orderByDesc('start_date')->get(),
            'teachers' => User::where('school_id', $schoolId)
                ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
                ->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function storeClassSection(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_room_id'    => 'required|exists:class_rooms,id',
            'section_id'       => 'required|exists:sections,id',
            'medium_id'        => 'required|exists:mediums,id',
            'academic_year_id' => 'required|exists:academic_years,id',
            'class_teacher_id' => 'nullable|exists:users,id',
        ]);
        $data['school_id'] = $this->schoolId();
        ClassSection::create($data);
        return back()->with('success', 'Class section ditambahkan.');
    }

    public function deleteClassSection(ClassSection $classSection): RedirectResponse
    {
        $this->authorizeOwn($classSection);
        $classSection->delete();
        return back()->with('success', 'Class section dihapus.');
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }
}
