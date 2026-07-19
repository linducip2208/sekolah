<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $query = BlogPost::with(['category', 'author'])
            ->latest();

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            if ($status === 'published') {
                $query->published();
            } elseif ($status === 'draft') {
                $query->where('is_published', false);
            }
        }

        if ($category = $request->get('category')) {
            $query->where('category_id', $category);
        }

        $posts = $query->paginate(15)->withQueryString();
        $categories = BlogCategory::orderBy('name')->get();

        return view('school-admin.blog.index', compact('posts', 'categories'));
    }

    public function create(): View
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('school-admin.blog.create', compact('categories'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => 'required|string|unique:blog_posts,slug',
            'category_id'      => 'nullable|exists:blog_categories,id',
            'content'          => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'featured_image'   => 'nullable|string|max:500',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:300',
            'is_published'     => 'boolean',
            'published_at'     => 'nullable|date',
        ]);

        $validated['author_id'] = auth()->id();

        if ($request->boolean('is_published') && !$validated['published_at']) {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);

        $this->pingIndexNow(route('blog.show', $post->slug));

        return redirect()->route('blog.index')
            ->with('success', "Artikel '{$post->title}' berhasil dibuat.");
    }

    public function edit(BlogPost $post): View
    {
        $categories = BlogCategory::orderBy('name')->get();
        return view('school-admin.blog.create', compact('post', 'categories'));
    }

    public function update(Request $request, BlogPost $post): RedirectResponse
    {
        $validated = $request->validate([
            'title'            => 'required|string|max:255',
            'slug'             => "required|string|unique:blog_posts,slug,{$post->id}",
            'category_id'      => 'nullable|exists:blog_categories,id',
            'content'          => 'required|string',
            'excerpt'          => 'nullable|string|max:500',
            'featured_image'   => 'nullable|string|max:500',
            'meta_title'       => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:300',
            'is_published'     => 'boolean',
            'published_at'     => 'nullable|date',
        ]);

        $wasUnpublished = !$post->is_published;

        if ($request->boolean('is_published') && !$validated['published_at']) {
            if ($wasUnpublished) {
                $validated['published_at'] = now();
            } else {
                $validated['published_at'] = $post->published_at ?? now();
            }
        }

        $post->update($validated);

        $this->pingIndexNow(route('blog.show', $post->slug));

        return redirect()->route('blog.index')
            ->with('success', "Artikel '{$post->title}' berhasil diperbarui.");
    }

    public function destroy(BlogPost $post): RedirectResponse
    {
        $title = $post->title;
        $post->delete();

        return redirect()->route('blog.index')
            ->with('success', "Artikel '{$title}' berhasil dihapus.");
    }

    public function categories(): View
    {
        $categories = BlogCategory::withCount('posts')->orderBy('name')->get();
        return view('school-admin.blog.categories', compact('categories'));
    }

    public function storeCategory(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => 'required|string|unique:blog_categories,slug',
            'description' => 'nullable|string|max:300',
        ]);

        BlogCategory::create($validated);

        return redirect()->route('blog.categories.index')
            ->with('success', "Kategori '{$validated['name']}' berhasil dibuat.");
    }

    public function updateCategory(Request $request, BlogCategory $category): RedirectResponse|JsonResponse
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:100',
            'slug'        => "required|string|unique:blog_categories,slug,{$category->id}",
            'description' => 'nullable|string|max:300',
        ]);

        $category->update($validated);

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Kategori berhasil diperbarui.', 'category' => $category->fresh()]);
        }

        return redirect()->route('blog.categories.index')
            ->with('success', "Kategori '{$validated['name']}' berhasil diperbarui.");
    }

    public function destroyCategory(Request $request, BlogCategory $category): RedirectResponse
    {
        if ($category->posts()->count() > 0) {
            return redirect()->route('blog.categories.index')
                ->withErrors(['name' => "Kategori '{$category->name}' masih memiliki artikel. Pindahkan atau hapus artikel terlebih dahulu."]);
        }

        $name = $category->name;
        $category->delete();

        return redirect()->route('blog.categories.index')
            ->with('success', "Kategori '{$name}' berhasil dihapus.");
    }

    private function pingIndexNow(string $url): void
    {
        if (!class_exists(\App\Services\Seo\IndexNowService::class)) {
            return;
        }

        try {
            app(\App\Services\Seo\IndexNowService::class)->submit([$url]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('IndexNow ping failed for blog post', [
                'url' => $url,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
