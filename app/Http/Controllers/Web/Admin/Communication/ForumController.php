<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\ForumCategory;
use App\Models\Communication\ForumTopic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ForumController extends Controller
{
    private function schoolId(): int
    {
        return auth()->user()->school_id;
    }

    // --- Categories ---
    public function categories(): View
    {
        $categories = ForumCategory::where('school_id', $this->schoolId())
            ->orderBy('sort_order')
            ->withCount('topics')
            ->get();

        return view('school-admin.forum.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        ForumCategory::create([
            'school_id'   => $this->schoolId(),
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'is_active'   => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('success', 'Kategori forum berhasil dibuat.');
    }

    public function updateCategory(Request $request, ForumCategory $category): RedirectResponse
    {
        $this->authorizeOwn($category);
        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string|max:500',
            'sort_order'  => 'nullable|integer|min:0',
            'is_active'   => 'nullable|boolean',
        ]);

        $category->update([
            'name'        => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order'  => $data['sort_order'] ?? 0,
            'is_active'   => (bool) ($data['is_active'] ?? true),
        ]);

        return back()->with('success', 'Kategori forum diperbarui.');
    }

    public function deleteCategory(ForumCategory $category): RedirectResponse
    {
        $this->authorizeOwn($category);
        $category->delete();
        return back()->with('success', 'Kategori forum dihapus.');
    }

    // --- Topics Management ---
    public function topics(): View
    {
        $topics = ForumTopic::where('school_id', $this->schoolId())
            ->with(['category', 'user:id,name'])
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('school-admin.forum.topics', compact('topics'));
    }

    public function togglePin(ForumTopic $topic): RedirectResponse
    {
        $this->authorizeOwn($topic);
        $topic->update(['is_pinned' => !$topic->is_pinned]);
        return back()->with('success', $topic->is_pinned ? 'Topik di-unpin.' : 'Topik di-pin.');
    }

    public function toggleLock(ForumTopic $topic): RedirectResponse
    {
        $this->authorizeOwn($topic);
        $topic->update(['is_locked' => !$topic->is_locked]);
        return back()->with('success', $topic->is_locked ? 'Topik dibuka kembali.' : 'Topik dikunci.');
    }

    public function deleteTopic(ForumTopic $topic): RedirectResponse
    {
        $this->authorizeOwn($topic);
        $topic->delete();
        return back()->with('success', 'Topik dihapus.');
    }

    private function authorizeOwn($model): void
    {
        abort_unless($model->school_id === $this->schoolId(), 403);
    }
}
