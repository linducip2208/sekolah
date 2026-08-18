<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Academic\Student;
use App\Models\Academic\StudentEnrollment;
use App\Models\Academic\StudentTag;
use App\Models\Academic\StudentTransfer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StudentLifecycleController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    /* ============== BATCH PROMOTION ============== */

    public function batchPromoteForm(): View
    {
        $schoolId = $this->schoolId();

        return view('school-admin.students.batch-promote', [
            'classSections' => ClassSection::where('school_id', $schoolId)
                ->with(['classRoom', 'section'])
                ->orderBy('class_room_id')
                ->get(),
            'enrollments' => StudentEnrollment::where('school_id', $schoolId)
                ->with(['student.user:id,name', 'fromClassSection.classRoom', 'toClassSection.classRoom'])
                ->orderByDesc('created_at')
                ->paginate(30),
        ]);
    }

    public function batchPromote(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'from_class_section_id' => 'required|exists:class_sections,id',
            'to_class_section_id'   => 'required|exists:class_sections,id|different:from_class_section_id',
            'academic_year'         => 'required|string|max:20',
            'effective_date'        => 'required|date',
        ]);

        $schoolId = $this->schoolId();

        $students = Student::where('school_id', $schoolId)
            ->where('class_section_id', $data['from_class_section_id'])
            ->where('status', 'active')
            ->get();

        if ($students->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa aktif di kelas asal.');
        }

        $count = 0;

        DB::transaction(function () use ($students, $data, $schoolId, &$count) {
            foreach ($students as $student) {
                StudentEnrollment::create([
                    'school_id'             => $schoolId,
                    'student_id'            => $student->id,
                    'from_class_section_id' => $data['from_class_section_id'],
                    'to_class_section_id'   => $data['to_class_section_id'],
                    'academic_year'         => $data['academic_year'],
                    'status'                => 'promoted',
                    'effective_date'        => $data['effective_date'],
                    'notes'                 => 'Kenaikan kelas massal',
                    'approved_by'           => auth()->id(),
                ]);

                $student->update(['class_section_id' => $data['to_class_section_id']]);
                $count++;
            }
        });

        return back()->with('success', "{$count} siswa berhasil dipromosikan.");
    }

    /* ============== TRANSFER ============== */

    public function transferForm(): View
    {
        $schoolId = $this->schoolId();

        return view('school-admin.students.transfer', [
            'students'  => Student::where('school_id', $schoolId)
                ->where('status', 'active')
                ->with('user:id,name')
                ->orderBy('admission_no')
                ->get(),
            'transfers' => StudentTransfer::where('school_id', $schoolId)
                ->with('student.user:id,name')
                ->orderByDesc('transfer_date')
                ->paginate(30),
        ]);
    }

    public function storeTransfer(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'      => 'required|exists:students,id',
            'to_school_name'  => 'required|string|max:200',
            'transfer_date'   => 'required|date',
            'reason'          => 'nullable|string',
            'document_no'     => 'nullable|string|max:100',
        ]);

        $schoolId = $this->schoolId();
        $student  = Student::where('school_id', $schoolId)->findOrFail($data['student_id']);

        DB::transaction(function () use ($student, $data, $schoolId) {
            $student->update([
                'status'         => 'transferred',
                'transferred_at' => $data['transfer_date'],
            ]);

            StudentTransfer::create([
                'school_id'       => $schoolId,
                'student_id'      => $student->id,
                'from_school_name' => auth()->user()->school?->name ?? '-',
                'to_school_name'  => $data['to_school_name'],
                'transfer_date'   => $data['transfer_date'],
                'reason'          => $data['reason'],
                'document_no'     => $data['document_no'] ?? null,
            ]);

            StudentEnrollment::create([
                'school_id'      => $schoolId,
                'student_id'     => $student->id,
                'academic_year'  => now()->year . '/' . (now()->year + 1),
                'status'         => 'transferred',
                'effective_date' => $data['transfer_date'],
                'notes'          => "Pindah ke {$data['to_school_name']}",
                'approved_by'    => auth()->id(),
            ]);
        });

        return back()->with('success', 'Siswa berhasil dipindahkan.');
    }

    /* ============== STUDENT TAGS ============== */

    public function tags(): View
    {
        $schoolId = $this->schoolId();

        return view('school-admin.students.tags', [
            'tags' => StudentTag::where('school_id', $schoolId)
                ->withCount('students')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storeTag(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'  => 'required|string|max:100',
            'color' => 'nullable|string|max:20',
        ]);

        $data['school_id'] = $this->schoolId();

        StudentTag::create($data);

        return back()->with('success', 'Tag ditambahkan.');
    }

    public function destroyTag(StudentTag $tag): RedirectResponse
    {
        abort_unless($tag->school_id === $this->schoolId(), 403);
        $tag->delete();
        return back()->with('success', 'Tag dihapus.');
    }

    public function tagStudent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'    => 'required|exists:students,id',
            'student_tag_id' => 'required|exists:student_tags,id',
        ]);

        $student = Student::where('school_id', $this->schoolId())->findOrFail($data['student_id']);

        $student->tags()->syncWithoutDetaching([$data['student_tag_id']]);

        return back()->with('success', 'Tag ditambahkan ke siswa.');
    }

    public function untagStudent(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'    => 'required|exists:students,id',
            'student_tag_id' => 'required|exists:student_tags,id',
        ]);

        $student = Student::where('school_id', $this->schoolId())->findOrFail($data['student_id']);

        $student->tags()->detach($data['student_tag_id']);

        return back()->with('success', 'Tag dihapus dari siswa.');
    }
}
