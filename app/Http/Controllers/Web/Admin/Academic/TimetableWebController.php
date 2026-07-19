<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Subject;
use App\Models\Academic\TimetableSlot;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableWebController extends Controller
{
    private const DAYS = [1=>'Senin', 2=>'Selasa', 3=>'Rabu', 4=>'Kamis', 5=>'Jumat', 6=>'Sabtu', 7=>'Minggu'];

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $schoolId = $this->schoolId();
        $classSectionId = $request->class_section_id;

        $slots = collect();
        if ($classSectionId) {
            $slots = TimetableSlot::where('school_id', $schoolId)
                ->where('class_section_id', $classSectionId)
                ->with(['subject', 'teacher:id,name'])
                ->orderBy('day_of_week')->orderBy('start_time')->get()
                ->groupBy('day_of_week');
        }

        return view('school-admin.timetable.index', [
            'slots'          => $slots,
            'classSections'  => ClassSection::where('school_id', $schoolId)->with(['classRoom', 'section'])->get(),
            'subjects'       => Subject::where('school_id', $schoolId)->orderBy('name')->get(),
            'teachers'       => User::where('school_id', $schoolId)
                ->whereHas('roles', fn ($q) => $q->where('name', 'teacher'))
                ->orderBy('name')->get(['id', 'name']),
            'days'           => self::DAYS,
            'classSectionId' => $classSectionId,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_section_id' => 'required|exists:class_sections,id',
            'subject_id'       => 'required|exists:subjects,id',
            'teacher_id'       => 'required|exists:users,id',
            'day_of_week'      => 'required|integer|min:1|max:7',
            'start_time'       => 'required|date_format:H:i',
            'end_time'         => 'required|date_format:H:i|after:start_time',
            'room'             => 'nullable|string|max:50',
        ]);
        $data['school_id'] = $this->schoolId();
        TimetableSlot::create($data);

        return back()->with('success', 'Slot jadwal ditambahkan.');
    }

    public function destroy(TimetableSlot $slot): RedirectResponse
    {
        abort_unless($slot->school_id === $this->schoolId(), 403);
        $slot->delete();
        return back()->with('success', 'Slot dihapus.');
    }
}
