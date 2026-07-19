<?php

namespace App\Http\Controllers\Web\Admin\Branding;

use App\Http\Controllers\Controller;
use App\Models\Website\SchoolContact;
use App\Models\Website\SchoolGallery;
use App\Models\Website\SchoolPage;
use App\Models\Website\SchoolPageSection;
use App\Models\Website\SchoolTestimonial;
use App\Services\Branding\BrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WebsiteBuilderController extends Controller
{
    public function __construct(private BrandingService $branding) {}

    public function pages(): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $pages = SchoolPage::orderBy('sort_order')->get();
        return view('school-admin.branding.website.pages', compact('branding', 'pages'));
    }

    public function storePage(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200|regex:/^[a-z0-9\-]+$/',
            'meta_description' => 'nullable|string|max:300',
            'status' => 'required|in:draft,published',
            'is_homepage' => 'nullable|boolean',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        $existing = SchoolPage::where('slug', $data['slug'])->first();
        if ($existing) {
            $data['slug'] = $data['slug'] . '-' . time();
        }

        if ($request->boolean('is_homepage')) {
            SchoolPage::where('is_homepage', true)
                ->where('school_id', auth()->user()->school_id)
                ->update(['is_homepage' => false]);
        }

        SchoolPage::create($data);

        return redirect()->route('admin.branding.website.pages')
            ->with('success', 'Halaman berhasil dibuat.');
    }

    public function updatePage(Request $request, SchoolPage $page): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'required|string|max:200',
            'slug' => 'nullable|string|max:200|regex:/^[a-z0-9\-]+$/',
            'meta_description' => 'nullable|string|max:300',
            'status' => 'required|in:draft,published',
            'is_homepage' => 'nullable|boolean',
        ]);

        $data['slug'] = $data['slug'] ?: Str::slug($data['title']);

        $existing = SchoolPage::where('slug', $data['slug'])
            ->where('id', '!=', $page->id)
            ->first();
        if ($existing) {
            $data['slug'] = $data['slug'] . '-' . time();
        }

        if ($request->boolean('is_homepage') && !$page->is_homepage) {
            SchoolPage::where('is_homepage', true)
                ->where('school_id', auth()->user()->school_id)
                ->update(['is_homepage' => false]);
        }

        $page->update($data);

        return redirect()->route('admin.branding.website.pages')
            ->with('success', 'Halaman berhasil diperbarui.');
    }

    public function deletePage(SchoolPage $page): RedirectResponse
    {
        $page->delete();
        return redirect()->route('admin.branding.website.pages')
            ->with('success', 'Halaman berhasil dihapus.');
    }

    public function builder(SchoolPage $page): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $sections = $page->sections()->orderBy('sort_order')->get();
        $sectionTypes = $this->sectionTypeOptions();
        return view('school-admin.branding.website.builder', compact('branding', 'page', 'sections', 'sectionTypes'));
    }

    public function storeSection(Request $request, SchoolPage $page): RedirectResponse
    {
        $data = $request->validate([
            'section_type' => 'required|string',
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:300',
            'content' => 'nullable|string',
            'config' => 'nullable|array',
            'sort_order' => 'nullable|integer',
        ]);

        $data['sort_order'] = $data['sort_order'] ?? ($page->sections()->max('sort_order') ?? 0) + 1;
        $data['content'] = $data['content'] ?? '';
        $data['config'] = $data['config'] ?? [];

        $page->sections()->create($data);

        return redirect()->route('admin.branding.website.builder', $page)
            ->with('success', 'Section berhasil ditambahkan.');
    }

    public function updateSection(Request $request, SchoolPageSection $section): RedirectResponse
    {
        $data = $request->validate([
            'title' => 'nullable|string|max:200',
            'subtitle' => 'nullable|string|max:300',
            'content' => 'nullable|string',
            'config' => 'nullable|array',
        ]);

        $data['config'] = $data['config'] ?? $section->config;
        $section->update($data);

        return back()->with('success', 'Section berhasil diperbarui.');
    }

    public function deleteSection(SchoolPageSection $section): RedirectResponse
    {
        $page = $section->page;
        $section->delete();
        return redirect()->route('admin.branding.website.builder', $page)
            ->with('success', 'Section berhasil dihapus.');
    }

    public function reorderSections(Request $request, SchoolPage $page): mixed
    {
        $orders = $request->input('orders', []);

        foreach ($orders as $item) {
            SchoolPageSection::where('id', $item['id'])
                ->where('school_page_id', $page->id)
                ->update(['sort_order' => $item['sort_order']]);
        }

        return response()->json(['success' => true]);
    }

    public function uploadSectionImage(Request $request, SchoolPageSection $section): RedirectResponse
    {
        $request->validate([
            'image' => 'required|image|max:5120|mimes:png,jpg,jpeg,webp',
        ]);

        $path = $request->file('image')->store(
            'website/' . auth()->user()->school_id . '/sections',
            'public'
        );

        if ($section->image_path) {
            Storage::disk('public')->delete($section->image_path);
        }

        $section->update(['image_path' => $path]);

        return back()->with('success', 'Gambar berhasil diunggah.');
    }

    public function gallery(): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $items = SchoolGallery::orderBy('sort_order')->get();
        return view('school-admin.branding.website.gallery', compact('branding', 'items'));
    }

    public function storeGallery(Request $request): RedirectResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:200',
            'image' => 'required|image|max:5120|mimes:png,jpg,jpeg,webp',
            'caption' => 'nullable|string|max:300',
            'is_published' => 'nullable|boolean',
        ]);

        $path = $request->file('image')->store(
            'website/' . auth()->user()->school_id . '/gallery',
            'public'
        );

        SchoolGallery::create([
            'title' => $request->input('title'),
            'file_path' => $path,
            'caption' => $request->input('caption'),
            'is_published' => $request->boolean('is_published', true),
            'sort_order' => (SchoolGallery::max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('admin.branding.website.gallery')
            ->with('success', 'Galeri berhasil ditambahkan.');
    }

    public function deleteGallery(SchoolGallery $gallery): RedirectResponse
    {
        Storage::disk('public')->delete($gallery->file_path);
        $gallery->delete();
        return redirect()->route('admin.branding.website.gallery')
            ->with('success', 'Galeri berhasil dihapus.');
    }

    public function updateGallery(Request $request, SchoolGallery $gallery): RedirectResponse
    {
        $request->validate([
            'title' => 'nullable|string|max:200',
            'caption' => 'nullable|string|max:300',
            'is_published' => 'nullable|boolean',
        ]);

        $gallery->update([
            'title' => $request->input('title'),
            'caption' => $request->input('caption'),
            'is_published' => $request->boolean('is_published', true),
        ]);

        return redirect()->route('admin.branding.website.gallery')
            ->with('success', 'Galeri berhasil diperbarui.');
    }

    public function testimonials(): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $items = SchoolTestimonial::orderBy('sort_order')->get();
        return view('school-admin.branding.website.testimonials', compact('branding', 'items'));
    }

    public function storeTestimonial(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|in:alumni,parent,student',
            'testimonial_text' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'photo' => 'nullable|image|max:2048|mimes:png,jpg,jpeg,webp',
            'is_published' => 'nullable|boolean',
        ]);

        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store(
                'website/' . auth()->user()->school_id . '/testimonials',
                'public'
            );
        }

        SchoolTestimonial::create([
            'name' => $request->input('name'),
            'role' => $request->input('role'),
            'photo_path' => $photoPath,
            'testimonial_text' => $request->input('testimonial_text'),
            'rating' => $request->integer('rating'),
            'is_published' => $request->boolean('is_published', false),
            'sort_order' => (SchoolTestimonial::max('sort_order') ?? 0) + 1,
        ]);

        return redirect()->route('admin.branding.website.testimonials')
            ->with('success', 'Testimoni berhasil ditambahkan.');
    }

    public function updateTestimonial(Request $request, SchoolTestimonial $testimonial): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|in:alumni,parent,student',
            'testimonial_text' => 'required|string|max:1000',
            'rating' => 'required|integer|min:1|max:5',
            'photo' => 'nullable|image|max:2048|mimes:png,jpg,jpeg,webp',
            'is_published' => 'nullable|boolean',
        ]);

        $data = [
            'name' => $request->input('name'),
            'role' => $request->input('role'),
            'testimonial_text' => $request->input('testimonial_text'),
            'rating' => $request->integer('rating'),
            'is_published' => $request->boolean('is_published', false),
        ];

        if ($request->hasFile('photo')) {
            if ($testimonial->photo_path) {
                Storage::disk('public')->delete($testimonial->photo_path);
            }
            $data['photo_path'] = $request->file('photo')->store(
                'website/' . auth()->user()->school_id . '/testimonials',
                'public'
            );
        }

        $testimonial->update($data);

        return redirect()->route('admin.branding.website.testimonials')
            ->with('success', 'Testimoni berhasil diperbarui.');
    }

    public function deleteTestimonial(SchoolTestimonial $testimonial): RedirectResponse
    {
        if ($testimonial->photo_path) {
            Storage::disk('public')->delete($testimonial->photo_path);
        }
        $testimonial->delete();
        return redirect()->route('admin.branding.website.testimonials')
            ->with('success', 'Testimoni berhasil dihapus.');
    }

    public function contacts(): View
    {
        $branding = $this->branding->getForSchool(auth()->user()->school_id);
        $messages = SchoolContact::orderBy('created_at', 'desc')->get();
        $unreadCount = SchoolContact::where('is_read', false)->count();
        return view('school-admin.branding.website.contacts', compact('branding', 'messages', 'unreadCount'));
    }

    public function markContactRead(SchoolContact $contact): RedirectResponse
    {
        $contact->update([
            'is_read' => true,
            'replied_at' => $contact->replied_at ?? now(),
        ]);
        return redirect()->route('admin.branding.website.contacts')
            ->with('success', 'Pesan ditandai sudah dibaca.');
    }

    public function deleteContact(SchoolContact $contact): RedirectResponse
    {
        $contact->delete();
        return redirect()->route('admin.branding.website.contacts')
            ->with('success', 'Pesan berhasil dihapus.');
    }

    protected function sectionTypeOptions(): array
    {
        return [
            'hero' => ['label' => 'Hero / Header', 'icon' => '🏠'],
            'about' => ['label' => 'Tentang', 'icon' => 'ℹ️'],
            'stats' => ['label' => 'Statistik', 'icon' => '📊'],
            'features' => ['label' => 'Fitur / Keunggulan', 'icon' => '⭐'],
            'gallery' => ['label' => 'Galeri Foto', 'icon' => '🖼️'],
            'testimonials' => ['label' => 'Testimoni', 'icon' => '💬'],
            'cta' => ['label' => 'Call to Action', 'icon' => '📣'],
            'contact' => ['label' => 'Kontak', 'icon' => '📞'],
            'news' => ['label' => 'Berita / Artikel', 'icon' => '📰'],
            'custom_html' => ['label' => 'Custom HTML', 'icon' => '💻'],
        ];
    }
}
