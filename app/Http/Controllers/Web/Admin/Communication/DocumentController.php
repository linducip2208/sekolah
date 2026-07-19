<?php

namespace App\Http\Controllers\Web\Admin\Communication;

use App\Http\Controllers\Controller;
use App\Models\Communication\Document;
use App\Models\Communication\DocumentApproval;
use App\Models\Communication\DocumentCategory;
use App\Models\Communication\DocumentShare;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class DocumentController extends Controller
{
    private int $schoolId;

    public function __construct()
    {
        $this->schoolId = auth()->user()->school_id;
    }

    public function index(Request $request): View
    {
        $query = Document::where('documents.school_id', $this->schoolId)
            ->with(['category', 'uploader:id,name']);

        if ($categoryId = $request->get('category_id')) {
            $query->where('document_category_id', $categoryId);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $sort = $request->get('sort', 'newest');
        $query->when($sort === 'oldest', fn($q) => $q->orderBy('created_at'))
              ->when($sort === 'name', fn($q) => $q->orderBy('title'))
              ->when($sort === 'downloads', fn($q) => $q->orderByDesc('download_count'))
              ->when($sort === 'newest' || ! in_array($sort, ['oldest', 'name', 'downloads']),
                  fn($q) => $q->orderByDesc('created_at'));

        $documents = $query->paginate(20)->withQueryString();

        $categories = DocumentCategory::where('school_id', $this->schoolId)
            ->withCount('documents')
            ->orderBy('name')
            ->get();

        $pendingApprovals = DocumentApproval::whereHas('document', function ($q) {
            $q->where('school_id', $this->schoolId);
        })->where('status', 'pending')->count();

        return view('school-admin.documents.index', compact(
            'documents', 'categories', 'pendingApprovals', 'sort', 'categoryId', 'search'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'document_category_id'=> 'nullable|exists:document_categories,id',
            'file'                => 'required|file|max:51200',
            'is_published'        => 'boolean',
        ]);

        $existing = Document::where('school_id', $this->schoolId)
            ->where('title', $data['title'])
            ->first();

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $storagePath = 'schools/' . $this->schoolId . '/documents/' . date('Y/m');

        if ($existing) {
            $oldPath = $existing->file_path;
            $newPath = $file->storeAs($storagePath, $originalName, 'public');

            $existing->update([
                'file_path'   => $newPath,
                'file_type'   => $file->getClientMimeType(),
                'file_size'   => $file->getSize(),
                'version'     => $existing->version + 1,
                'user_id'     => auth()->id(),
                'description' => $data['description'] ?? $existing->description,
                'is_published'=> $data['is_published'] ?? $existing->is_published,
                'document_category_id' => $data['document_category_id'] ?? $existing->document_category_id,
            ]);

            return back()->with('success', "Dokumen '{$data['title']}' diperbarui ke versi {$existing->version}.");
        }

        $path = $file->storeAs($storagePath, $originalName, 'public');

        Document::create([
            'school_id'           => $this->schoolId,
            'title'               => $data['title'],
            'description'         => $data['description'] ?? null,
            'document_category_id'=> $data['document_category_id'] ?? null,
            'file_path'           => $path,
            'file_type'           => $file->getClientMimeType(),
            'file_size'           => $file->getSize(),
            'version'             => 1,
            'user_id'             => auth()->id(),
            'is_published'        => $data['is_published'] ?? false,
            'published_at'        => ($data['is_published'] ?? false) ? now() : null,
        ]);

        return back()->with('success', "Dokumen '{$data['title']}' berhasil diunggah.");
    }

    public function update(Request $request, Document $document): RedirectResponse
    {
        abort_unless($document->school_id === $this->schoolId, 403);

        $data = $request->validate([
            'title'               => 'required|string|max:255',
            'description'         => 'nullable|string',
            'document_category_id'=> 'nullable|exists:document_categories,id',
            'is_published'        => 'boolean',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $storagePath = 'schools/' . $this->schoolId . '/documents/' . date('Y/m');
            $path = $file->storeAs($storagePath, $file->getClientOriginalName(), 'public');

            $data['file_path'] = $path;
            $data['file_type'] = $file->getClientMimeType();
            $data['file_size'] = $file->getSize();
            $data['version']   = $document->version + 1;
            $data['user_id']   = auth()->id();
        }

        if ($data['is_published'] ?? false) {
            $data['published_at'] = $data['published_at'] ?? now();
        }

        $document->update($data);
        return back()->with('success', "Dokumen '{$document->title}' diperbarui.");
    }

    public function destroy(Document $document): RedirectResponse
    {
        abort_unless($document->school_id === $this->schoolId, 403);
        $title = $document->title;
        $document->delete();
        return back()->with('success', "Dokumen '{$title}' dihapus.");
    }

    public function download(Document $document)
    {
        abort_unless($document->school_id === $this->schoolId, 403);

        $document->increment('download_count');

        $path = storage_path('app/public/' . $document->file_path);
        if (! file_exists($path)) {
            return back()->with('error', 'File tidak ditemukan.');
        }

        return response()->download($path, basename($document->file_path), [
            'Content-Type' => $document->file_type ?? 'application/octet-stream',
        ]);
    }

    public function approvals(Request $request): View
    {
        $approvals = DocumentApproval::where('status', 'pending')
            ->whereHas('document', function ($q) {
                $q->where('school_id', $this->schoolId);
            })
            ->with(['document.category', 'document.uploader:id,name', 'approver:id,name'])
            ->paginate(20);

        return view('school-admin.documents.approvals', compact('approvals'));
    }

    public function decideApproval(Request $request, DocumentApproval $approval): RedirectResponse
    {
        $doc = $approval->document;
        abort_unless($doc->school_id === $this->schoolId, 403);

        $data = $request->validate([
            'decision' => 'required|in:approved,rejected',
            'notes'    => 'nullable|string',
        ]);

        $approval->update([
            'status'     => $data['decision'],
            'notes'      => $data['notes'],
            'decided_at' => now(),
        ]);

        if ($data['decision'] === 'approved') {
            $doc->update(['is_published' => true, 'published_at' => now()]);
        }

        $label = $data['decision'] === 'approved' ? 'disetujui' : 'ditolak';
        return back()->with('success', "Dokumen '{$doc->title}' {$label}.");
    }

    public function share(Request $request, Document $document): RedirectResponse
    {
        abort_unless($document->school_id === $this->schoolId, 403);

        $data = $request->validate([
            'shared_with_type' => 'required|in:user,role,school',
            'shared_with_id'   => 'required|integer',
            'expires_days'     => 'nullable|integer|min:1|max:365',
        ]);

        $share = DocumentShare::create([
            'document_id'      => $document->id,
            'shared_by'        => auth()->id(),
            'shared_with_type' => $data['shared_with_type'],
            'shared_with_id'   => (int) $data['shared_with_id'],
            'expires_at'       => isset($data['expires_days']) ? now()->addDays($data['expires_days']) : null,
            'access_token'     => Str::random(64),
            'is_active'        => true,
        ]);

        $url = route('admin.documents.shared', ['token' => $share->access_token]);
        return back()->with('success', "Link berbagi dibuat: <code>{$url}</code>");
    }

    public function revokeShare(DocumentShare $share): RedirectResponse
    {
        $share->update(['is_active' => false]);
        return back()->with('success', 'Link berbagi dicabut.');
    }

    public function sharedAccess(string $token)
    {
        $share = DocumentShare::where('access_token', $token)
            ->where('is_active', true)
            ->with('document.uploader:id,name')
            ->first();

        if (! $share) {
            abort(404, 'Link berbagi tidak valid atau sudah kadaluarsa.');
        }

        if ($share->expires_at && $share->expires_at->isPast()) {
            $share->update(['is_active' => false]);
            abort(410, 'Link berbagi sudah kadaluarsa.');
        }

        $document = $share->document;
        $document->increment('download_count');

        $path = storage_path('app/public/' . $document->file_path);
        if (! file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        return response()->download($path, basename($document->file_path), [
            'Content-Type' => $document->file_type ?? 'application/octet-stream',
        ]);
    }

    public function categoriesIndex(Request $request): View
    {
        $categories = DocumentCategory::where('school_id', $this->schoolId)
            ->withCount('documents')
            ->with('children')
            ->orderBy('name')
            ->get();

        $parentCategories = $categories->whereNull('parent_id');

        return view('school-admin.documents.categories', compact('categories', 'parentCategories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'parent_id'    => 'nullable|exists:document_categories,id',
            'description'  => 'nullable|string',
            'access_level' => 'required|in:public,staff,admin,confidential',
        ]);
        $data['school_id'] = $this->schoolId;

        DocumentCategory::create($data);
        return back()->with('success', 'Kategori dokumen dibuat.');
    }

    public function updateCategory(Request $request, DocumentCategory $category): RedirectResponse
    {
        abort_unless($category->school_id === $this->schoolId, 403);

        $data = $request->validate([
            'name'         => 'required|string|max:255',
            'parent_id'    => 'nullable|exists:document_categories,id',
            'description'  => 'nullable|string',
            'access_level' => 'required|in:public,staff,admin,confidential',
        ]);

        $category->update($data);
        return back()->with('success', 'Kategori diperbarui.');
    }

    public function deleteCategory(DocumentCategory $category): RedirectResponse
    {
        abort_unless($category->school_id === $this->schoolId, 403);
        $category->delete();
        return back()->with('success', 'Kategori dihapus.');
    }
}
