<?php

namespace App\Http\Controllers\Web\Admin\Academic;

use App\Http\Controllers\Controller;
use App\Models\Curriculum\CurriculumFramework;
use App\Models\Curriculum\CurriculumVersion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumVersionController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }

    public function index(): View
    {
        $schoolId = $this->schoolId();

        return view('school-admin.curriculum.versions', [
            'versions'   => CurriculumVersion::where('school_id', $schoolId)
                ->with('framework:id,name,type')
                ->orderByDesc('created_at')
                ->get(),
            'frameworks' => CurriculumFramework::where('school_id', $schoolId)
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'curriculum_framework_id' => 'required|exists:curriculum_frameworks,id',
            'version_name'            => 'required|string|max:100',
            'academic_year'           => 'nullable|string|max:20',
            'effective_date'          => 'nullable|date',
            'notes'                   => 'nullable|string',
        ]);

        $data['school_id'] = $this->schoolId();
        $data['is_active'] = false;

        CurriculumVersion::create($data);

        return back()->with('success', 'Versi kurikulum ditambahkan.');
    }

    public function update(Request $request, CurriculumVersion $version): RedirectResponse
    {
        $this->authorizeOwn($version);

        $data = $request->validate([
            'version_name'   => 'required|string|max:100',
            'academic_year'  => 'nullable|string|max:20',
            'effective_date' => 'nullable|date',
            'notes'          => 'nullable|string',
        ]);

        $version->update($data);

        return back()->with('success', 'Versi kurikulum diperbarui.');
    }

    public function activate(CurriculumVersion $version): RedirectResponse
    {
        $this->authorizeOwn($version);

        CurriculumVersion::where('school_id', $this->schoolId())
            ->where('curriculum_framework_id', $version->curriculum_framework_id)
            ->update(['is_active' => false]);

        $version->update(['is_active' => true]);

        return back()->with('success', "Versi '{$version->version_name}' diaktifkan.");
    }

    public function destroy(CurriculumVersion $version): RedirectResponse
    {
        $this->authorizeOwn($version);
        $version->delete();
        return back()->with('success', 'Versi kurikulum dihapus.');
    }
}
