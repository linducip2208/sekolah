<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Academic\ClassSection;
use App\Models\Communication\Notice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NoticeWebController extends Controller
{
    private const ROLES = ['parent', 'student', 'teacher', 'admin', 'accountant'];

    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    public function index(): View
    {
        return view('school-admin.notices.index', [
            'notices' => Notice::where('school_id', $this->schoolId())
                ->with('creator:id,name')
                ->orderByDesc('created_at')
                ->paginate(20),
        ]);
    }

    public function create(): View
    {
        return view('school-admin.notices.create', [
            'roles'         => self::ROLES,
            'classSections' => ClassSection::where('school_id', $this->schoolId())
                ->with(['classRoom', 'section'])->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'                   => 'required|string|max:255',
            'content'                 => 'required|string|max:10000',
            'target_roles'            => 'nullable|array',
            'target_roles.*'          => 'in:'.implode(',', self::ROLES),
            'target_class_sections'   => 'nullable|array',
            'target_class_sections.*' => 'exists:class_sections,id',
            'publish_at'              => 'nullable|date',
            'expire_at'               => 'nullable|date|after_or_equal:publish_at',
            'is_published'            => 'nullable|boolean',
        ]);

        Notice::create([
            'school_id'             => $this->schoolId(),
            'created_by'            => auth()->id(),
            'title'                 => $data['title'],
            'content'               => $data['content'],
            'target_roles'          => $data['target_roles'] ?? [],
            'target_class_sections' => $data['target_class_sections'] ?? [],
            'publish_at'            => $data['publish_at'] ?? now(),
            'expire_at'             => $data['expire_at'] ?? null,
            'is_published'          => (bool) ($data['is_published'] ?? true),
        ]);

        return redirect()->route('admin.notices.index')->with('success', 'Pengumuman dipublikasikan.');
    }

    public function edit(Notice $notice): View
    {
        $this->authorizeOwn($notice);
        return view('school-admin.notices.edit', [
            'notice'        => $notice,
            'roles'         => self::ROLES,
            'classSections' => ClassSection::where('school_id', $this->schoolId())
                ->with(['classRoom', 'section'])->get(),
        ]);
    }

    public function update(Request $request, Notice $notice): RedirectResponse
    {
        $this->authorizeOwn($notice);
        $data = $request->validate([
            'title'                   => 'required|string|max:255',
            'content'                 => 'required|string|max:10000',
            'target_roles'            => 'nullable|array',
            'target_class_sections'   => 'nullable|array',
            'publish_at'              => 'nullable|date',
            'expire_at'               => 'nullable|date|after_or_equal:publish_at',
            'is_published'            => 'nullable|boolean',
        ]);
        $notice->update([
            'title'                 => $data['title'],
            'content'               => $data['content'],
            'target_roles'          => $data['target_roles'] ?? [],
            'target_class_sections' => $data['target_class_sections'] ?? [],
            'publish_at'            => $data['publish_at'] ?? $notice->publish_at,
            'expire_at'             => $data['expire_at'] ?? null,
            'is_published'          => (bool) ($data['is_published'] ?? false),
        ]);
        return redirect()->route('admin.notices.index')->with('success', 'Pengumuman diperbarui.');
    }

    public function destroy(Notice $notice): RedirectResponse
    {
        $this->authorizeOwn($notice);
        $notice->delete();
        return back()->with('success', 'Pengumuman dihapus.');
    }

    private function authorizeOwn(Notice $notice): void
    {
        abort_unless($notice->school_id === $this->schoolId(), 403);
    }
}
