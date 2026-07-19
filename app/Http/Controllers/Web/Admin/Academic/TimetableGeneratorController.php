<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\AcademicYear;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Staff;
use App\Models\Academic\Subject;
use App\Models\Academic\TeacherAvailability;
use App\Models\Academic\TimetableConfig;
use App\Models\Academic\TimetableSlot;
use App\Models\User;
use App\Services\TimetableGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TimetableGeneratorController extends Controller
{
    private int $schoolId;

    public function __construct()
    {
        $this->schoolId = auth()->user()->school_id;
    }

    public function wizard(Request $request): View
    {
        $step = (int) ($request->get('step', 1));
        $step = max(1, min(5, $step));

        $viewData = match ($step) {
            1 => $this->step1Data(),
            2 => $this->step2Data($request),
            3 => $this->step3Data($request),
            4 => $this->step4Data($request),
            5 => $this->step5Data($request),
            default => $this->step1Data(),
        };

        return view('school-admin.timetable.generator', array_merge($viewData, [
            'step' => $step,
        ]));
    }

    public function postStep(Request $request): RedirectResponse
    {
        $step = (int) ($request->get('step', 1));
        $nextStep = min(5, $step + 1);

        if ($step === 1) {
            $request->validate([
                'academic_year_id'  => 'required|exists:academic_years,id',
                'class_section_ids' => 'required|array|min:1',
                'class_section_ids.*' => 'exists:class_sections,id',
            ]);
            session(['timetable_gen' => [
                'academic_year_id'  => (int) $request->academic_year_id,
                'class_section_ids' => array_map('intval', $request->class_section_ids),
            ]]);
        }

        if ($step === 2) {
            $gen = session('timetable_gen', []);
            foreach ($gen['class_section_ids'] ?? [] as $csId) {
                $request->validate([
                    "config.{$csId}.days_per_week"    => 'required|integer|min:5|max:6',
                    "config.{$csId}.periods_per_day"  => 'required|integer|min:3|max:15',
                    "config.{$csId}.duration"         => 'required|integer|min:25|max:90',
                    "config.{$csId}.start_time"       => 'required|date_format:H:i',
                ]);
            }
            $gen['configs'] = $request->config ?? [];
            session(['timetable_gen' => $gen]);
        }

        if ($step === 3) {
            $availData = $request->availability ?? [];
            $gen = session('timetable_gen', []);
            $this->saveTeacherAvailability($availData);
            $gen['availability'] = $availData;
            session(['timetable_gen' => $gen]);
        }

        if ($step === 4) {
            $hoursData = $request->hours ?? [];
            $gen = session('timetable_gen', []);
            $this->saveSubjectHours($hoursData);
            $gen['subject_hours'] = $hoursData;
            session(['timetable_gen' => $gen]);
        }

        return redirect()->route('admin.timetable.generator.wizard', ['step' => $nextStep]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $gen = session('timetable_gen', []);
        $classSectionIds = $gen['class_section_ids'] ?? [];
        $academicYearId = $gen['academic_year_id'] ?? null;

        if (empty($classSectionIds) || ! $academicYearId) {
            return redirect()->route('admin.timetable.generator.wizard', ['step' => 1])
                ->with('error', 'Silakan mulai dari langkah 1.');
        }

        $configOverrides = [];
        foreach ($gen['configs'] ?? [] as $csId => $cfg) {
            $configOverrides[$csId] = [
                'days_per_week'           => (int) ($cfg['days_per_week'] ?? 5),
                'periods_per_day'         => (int) ($cfg['periods_per_day'] ?? 8),
                'period_duration_minutes' => (int) ($cfg['duration'] ?? 45),
                'break_after_periods'     => isset($cfg['breaks']) ? array_map('intval', explode(',', $cfg['breaks'])) : [],
                'start_time'              => $cfg['start_time'] ?? '07:00',
            ];
        }

        if (count($classSectionIds) > 3) {
            dispatch(new \App\Jobs\GenerateTimetableJob(
                $this->schoolId, $classSectionIds, $academicYearId, $configOverrides
            ));
            return redirect()->route('admin.timetable.generator.wizard', ['step' => 5])
                ->with('success', 'Generate jadwal diantrekan. Silakan refresh untuk melihat hasilnya.');
        }

        $service = new TimetableGeneratorService($this->schoolId);

        foreach ($classSectionIds as $csId) {
            $service->clearExistingSlots($csId);
        }

        $results = $service->generate($classSectionIds, $academicYearId, $configOverrides);

        $allSlots = [];
        foreach ($results as $csId => $slots) {
            $service->saveSlots($slots);
            $allSlots[$csId] = $slots;
        }

        session(['timetable_gen_results' => $allSlots]);
        session(['timetable_gen_warnings' => $service->getWarnings()]);

        return redirect()->route('admin.timetable.generator.wizard', ['step' => 5]);
    }

    public function saveConfig(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'academic_year_id'       => 'required|exists:academic_years,id',
            'class_section_id'       => 'required|exists:class_sections,id',
            'days_per_week'          => 'required|integer|min:5|max:6',
            'periods_per_day'        => 'required|integer|min:3|max:15',
            'period_duration_minutes'=> 'required|integer|min:25|max:90',
            'break_after_periods'    => 'nullable|array',
            'start_time'             => 'required|date_format:H:i',
        ]);
        $data['school_id'] = $this->schoolId;

        TimetableConfig::updateOrCreate(
            [
                'school_id'        => $this->schoolId,
                'academic_year_id' => $data['academic_year_id'],
                'class_section_id' => $data['class_section_id'],
            ],
            $data
        );

        return back()->with('success', 'Konfigurasi jadwal disimpan.');
    }

    private function step1Data(): array
    {
        return [
            'academicYears' => AcademicYear::where('school_id', $this->schoolId)->orderByDesc('start_date')->get(),
            'classSections' => ClassSection::where('school_id', $this->schoolId)
                ->with(['classRoom', 'section', 'subjects'])
                ->orderBy('id')->get(),
            'gen' => session('timetable_gen', []),
        ];
    }

    private function step2Data(Request $request): array
    {
        $gen = session('timetable_gen', []);
        $classSectionIds = $gen['class_section_ids'] ?? [];
        $classSections = ClassSection::where('school_id', $this->schoolId)
            ->whereIn('id', $classSectionIds)
            ->with(['classRoom', 'section'])
            ->get();

        $existingConfigs = [];
        foreach ($classSections as $cs) {
            $cfg = TimetableConfig::where('school_id', $this->schoolId)
                ->where('academic_year_id', $gen['academic_year_id'] ?? 0)
                ->where('class_section_id', $cs->id)
                ->first();
            $existingConfigs[$cs->id] = $cfg;
        }

        return [
            'classSections'   => $classSections,
            'gen'             => $gen,
            'existingConfigs' => $existingConfigs,
        ];
    }

    private function step3Data(Request $request): array
    {
        $gen = session('timetable_gen', []);
        $classSectionIds = $gen['class_section_ids'] ?? [];
        $classSections = ClassSection::where('school_id', $this->schoolId)
            ->whereIn('id', $classSectionIds)
            ->with(['subjects' => function ($q) {
                $q->withPivot('teacher_id');
            }])
            ->get();

        $teacherIds = [];
        foreach ($classSections as $cs) {
            foreach ($cs->subjects as $subj) {
                $tid = $subj->pivot->teacher_id ?? null;
                if ($tid) $teacherIds[] = $tid;
            }
        }
        $teacherIds = array_unique($teacherIds);

        $teachers = User::where('school_id', $this->schoolId)
            ->whereIn('id', $teacherIds)
            ->orderBy('name')
            ->get();

        $staffMap = Staff::where('school_id', $this->schoolId)
            ->whereIn('user_id', $teacherIds)
            ->pluck('id', 'user_id');

        $availability = [];
        $availRecords = TeacherAvailability::where('school_id', $this->schoolId)
            ->whereIn('staff_id', $staffMap->values())
            ->get()
            ->groupBy('staff_id');

        foreach ($teachers as $teacher) {
            $staffId = $staffMap[$teacher->id] ?? null;
            if (! $staffId) continue;

            for ($d = 1; $d <= 6; $d++) {
                if (isset($availRecords[$staffId])) {
                    $rec = $availRecords[$staffId]->firstWhere('day_of_week', $d);
                    $availability[$teacher->id][$d] = $rec?->is_available ?? true;
                } else {
                    $availability[$teacher->id][$d] = true;
                }
            }
        }

        return [
            'teachers'     => $teachers,
            'staffMap'     => $staffMap,
            'availability' => $availability,
            'gen'          => $gen,
            'daysList'     => TimetableGeneratorService::daysList(),
        ];
    }

    private function step4Data(Request $request): array
    {
        $gen = session('timetable_gen', []);
        $classSectionIds = $gen['class_section_ids'] ?? [];
        $classSections = ClassSection::where('school_id', $this->schoolId)
            ->whereIn('id', $classSectionIds)
            ->with(['subjects' => function ($q) {
                $q->withPivot('teacher_id');
            }])
            ->get();

        $subjectAllocations = [];
        foreach ($classSections as $cs) {
            foreach ($cs->subjects as $subject) {
                $key = "{$cs->id}_{$subject->id}";
                $subjectAllocations[$key] = [
                    'class_section_id' => $cs->id,
                    'class_section'    => ($cs->classRoom?->name ?? '') . ' ' . ($cs->section?->name ?? ''),
                    'subject_id'       => $subject->id,
                    'subject_name'     => $subject->name,
                    'teacher_id'       => $subject->pivot->teacher_id ?? null,
                    'hours'            => $subject->credit_hours ?? 2,
                ];
            }
        }

        return [
            'subjectAllocations' => $subjectAllocations,
            'gen'                => $gen,
        ];
    }

    private function step5Data(Request $request): array
    {
        $gen = session('timetable_gen', []);
        $results = session('timetable_gen_results', []);
        $warnings = session('timetable_gen_warnings', []);

        $classSections = ClassSection::where('school_id', $this->schoolId)
            ->whereIn('id', $gen['class_section_ids'] ?? [])
            ->with(['classRoom', 'section'])
            ->get();

        $daysList = TimetableGeneratorService::daysList();

        $generatedSlots = [];
        if (! empty($results)) {
            foreach ($results as $csId => $slots) {
                $grouped = [];
                foreach ($slots as $slot) {
                    $day = $slot['day_of_week'];
                    $grouped[$day][] = $slot;
                }
                $generatedSlots[$csId] = $grouped;
            }
        }

        return [
            'classSections'  => $classSections,
            'generatedSlots' => $generatedSlots,
            'warnings'       => $warnings,
            'daysList'       => $daysList,
            'gen'            => $gen,
        ];
    }

    private function saveTeacherAvailability(array $availData): void
    {
        $staffMap = Staff::where('school_id', $this->schoolId)
            ->pluck('id', 'user_id');

        foreach ($availData as $teacherId => $days) {
            $staffId = $staffMap[$teacherId] ?? null;
            if (! $staffId) continue;

            foreach ($days as $day => $available) {
                TeacherAvailability::updateOrCreate(
                    [
                        'school_id'   => $this->schoolId,
                        'staff_id'    => $staffId,
                        'day_of_week' => (int) $day,
                    ],
                    [
                        'is_available' => (bool) $available,
                    ]
                );
            }
        }
    }

    private function saveSubjectHours(array $hoursData): void
    {
        foreach ($hoursData as $subjectId => $hours) {
            Subject::where('id', $subjectId)
                ->where('school_id', $this->schoolId)
                ->update(['credit_hours' => (int) $hours]);
        }
    }
}
