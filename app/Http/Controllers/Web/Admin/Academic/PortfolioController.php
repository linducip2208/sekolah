<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Academic\Student;
use App\Models\Academic\StudentPortfolio;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortfolioController extends Controller
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
        $query = StudentPortfolio::where('school_id', $schoolId)
            ->with(['student.user:id,name'])
            ->orderByDesc('created_at');

        if ($request->filled('student_id')) {
            $query->where('student_id', $request->input('student_id'));
        }
        if ($request->filled('portfolio_type')) {
            $query->where('portfolio_type', $request->input('portfolio_type'));
        }
        if ($request->filled('approved')) {
            $filter = $request->input('approved');
            if ($filter === 'yes') {
                $query->whereNotNull('approved_at');
            } elseif ($filter === 'no') {
                $query->whereNull('approved_at');
            }
        }

        $portfolios = $query->paginate(24);
        $students = Student::where('school_id', $schoolId)->with('user:id,name')->orderBy('admission_no')->get();

        $typeLabels = [
            'academic'    => 'Akademik',
            'achievement' => 'Prestasi',
            'project'     => 'Proyek',
            'certificate' => 'Sertifikat',
            'artwork'     => 'Karya Seni',
            'other'       => 'Lainnya',
        ];

        return view('school-admin.academic.portfolios.index', compact('portfolios', 'students', 'typeLabels'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id'     => 'required|exists:students,id',
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'portfolio_type' => 'required|string|in:academic,achievement,project,certificate,artwork,other',
            'file'           => 'nullable|file|max:20480',
            'thumbnail'      => 'nullable|file|max:5120',
            'url'            => 'nullable|url|max:500',
            'tags'           => 'nullable|string',
        ]);

        $student = Student::findOrFail($data['student_id']);
        abort_unless($student->school_id === $this->schoolId(), 403);

        $portfolio = new StudentPortfolio();
        $portfolio->school_id = $this->schoolId();
        $portfolio->student_id = $student->id;
        $portfolio->title = $data['title'];
        $portfolio->description = $data['description'] ?? null;
        $portfolio->portfolio_type = $data['portfolio_type'];
        $portfolio->url = $data['url'] ?? null;
        $portfolio->tags = $data['tags'] ? array_map('trim', explode(',', $data['tags'])) : [];
        $portfolio->is_public = false;
        $portfolio->share_token = Str::random(40);

        if ($request->hasFile('file')) {
            $portfolio->file_path = $request->file('file')->store('portfolios/' . $this->schoolId(), 'public');
        }
        if ($request->hasFile('thumbnail')) {
            $portfolio->thumbnail_path = $request->file('thumbnail')->store('portfolios/' . $this->schoolId() . '/thumbnails', 'public');
        }

        $portfolio->save();

        return back()->with('success', 'Portofolio berhasil ditambahkan.');
    }

    public function update(Request $request, StudentPortfolio $portfolio): RedirectResponse
    {
        $this->authorizeOwn($portfolio);

        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'portfolio_type' => 'required|string|in:academic,achievement,project,certificate,artwork,other',
            'file'           => 'nullable|file|max:20480',
            'thumbnail'      => 'nullable|file|max:5120',
            'url'            => 'nullable|url|max:500',
            'tags'           => 'nullable|string',
        ]);

        $portfolio->title = $data['title'];
        $portfolio->description = $data['description'] ?? null;
        $portfolio->portfolio_type = $data['portfolio_type'];
        $portfolio->url = $data['url'] ?? null;
        $portfolio->tags = $data['tags'] ? array_map('trim', explode(',', $data['tags'])) : [];

        if ($request->hasFile('file')) {
            $portfolio->file_path = $request->file('file')->store('portfolios/' . $this->schoolId(), 'public');
        }
        if ($request->hasFile('thumbnail')) {
            $portfolio->thumbnail_path = $request->file('thumbnail')->store('portfolios/' . $this->schoolId() . '/thumbnails', 'public');
        }

        $portfolio->save();

        return back()->with('success', 'Portofolio diperbarui.');
    }

    public function approve(StudentPortfolio $portfolio): RedirectResponse
    {
        $this->authorizeOwn($portfolio);
        $portfolio->update([
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);
        return back()->with('success', 'Portofolio disetujui.');
    }

    public function reject(StudentPortfolio $portfolio): RedirectResponse
    {
        $this->authorizeOwn($portfolio);
        $portfolio->update([
            'approved_by' => null,
            'approved_at' => null,
        ]);
        return back()->with('success', 'Portofolio ditolak / dibatalkan.');
    }

    public function destroy(StudentPortfolio $portfolio): RedirectResponse
    {
        $this->authorizeOwn($portfolio);
        $portfolio->delete();
        return back()->with('success', 'Portofolio dihapus.');
    }

    /* =================== STUDENT PORTAL =================== */

    public function studentIndex(): View
    {
        $student = Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404);

        $portfolios = StudentPortfolio::where('student_id', $student->id)
            ->orderByDesc('created_at')
            ->paginate(15);

        $typeLabels = [
            'academic'    => 'Akademik',
            'achievement' => 'Prestasi',
            'project'     => 'Proyek',
            'certificate' => 'Sertifikat',
            'artwork'     => 'Karya Seni',
            'other'       => 'Lainnya',
        ];

        return view('student-portal.portfolios.index', compact('student', 'portfolios', 'typeLabels'));
    }

    public function studentStore(Request $request): RedirectResponse
    {
        $student = Student::where('user_id', auth()->id())->first();
        abort_unless($student, 404);

        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'description'    => 'nullable|string',
            'portfolio_type' => 'required|string|in:academic,achievement,project,certificate,artwork,other',
            'file'           => 'nullable|file|max:20480',
            'thumbnail'      => 'nullable|file|max:5120',
            'url'            => 'nullable|url|max:500',
            'tags'           => 'nullable|string',
        ]);

        $portfolio = new StudentPortfolio();
        $portfolio->school_id = $student->school_id;
        $portfolio->student_id = $student->id;
        $portfolio->title = $data['title'];
        $portfolio->description = $data['description'] ?? null;
        $portfolio->portfolio_type = $data['portfolio_type'];
        $portfolio->url = $data['url'] ?? null;
        $portfolio->tags = $data['tags'] ? array_map('trim', explode(',', $data['tags'])) : [];
        $portfolio->is_public = false;
        $portfolio->share_token = Str::random(40);

        if ($request->hasFile('file')) {
            $portfolio->file_path = $request->file('file')->store('portfolios/' . $student->school_id, 'public');
        }
        if ($request->hasFile('thumbnail')) {
            $portfolio->thumbnail_path = $request->file('thumbnail')->store('portfolios/' . $student->school_id . '/thumbnails', 'public');
        }

        $portfolio->save();

        return redirect()->route('student.portfolios')->with('success', 'Portofolio berhasil ditambahkan.');
    }

    public function studentDestroy(StudentPortfolio $portfolio): RedirectResponse
    {
        $student = Student::where('user_id', auth()->id())->first();
        abort_unless($student && $portfolio->student_id === $student->id, 403);

        $portfolio->delete();
        return redirect()->route('student.portfolios')->with('success', 'Portofolio dihapus.');
    }
}
