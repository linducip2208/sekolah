<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\School;
use App\Models\Website\SchoolContact;
use App\Models\Website\SchoolGallery;
use App\Models\Website\SchoolPage;
use App\Models\Website\SchoolPageSection;
use App\Models\Website\SchoolTestimonial;
use App\Services\Branding\BrandingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolWebsiteController extends Controller
{
    public function __construct(private BrandingService $branding) {}

    public function homepage(string $subdomain): View
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $branding = $this->branding->getForSchool($school->id);

        $page = $this->findHomepage($school->id);

        if (!$page) {
            abort(404, 'Website sekolah belum dikonfigurasi.');
        }

        $sections = $this->getPublishedSections($page->id);

        return view('school-website.homepage', compact('school', 'branding', 'page', 'sections'));
    }

    public function customPage(string $subdomain, string $slug): View
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();
        $branding = $this->branding->getForSchool($school->id);

        $page = $this->findPageBySlug($school->id, $slug);

        if (!$page) {
            abort(404, 'Halaman tidak ditemukan.');
        }

        $sections = $this->getPublishedSections($page->id);

        return view('school-website.custom-page', compact('school', 'branding', 'page', 'sections'));
    }

    public function postContact(Request $request, string $subdomain): RedirectResponse
    {
        $school = School::where('subdomain', $subdomain)->firstOrFail();

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100',
            'phone' => 'nullable|string|max:30',
            'message' => 'required|string|max:2000',
        ]);

        SchoolContact::create([
            'school_id' => $school->id,
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'message' => $request->input('message'),
        ]);

        return back()->with('success', 'Pesan Anda telah terkirim. Kami akan menghubungi Anda segera.');
    }

    protected function findHomepage(int $schoolId): ?SchoolPage
    {
        return SchoolPage::where('school_id', $schoolId)
            ->where('is_homepage', true)
            ->where('status', 'published')
            ->first()
            ?? SchoolPage::where('school_id', $schoolId)
                ->where('status', 'published')
                ->orderBy('sort_order')
                ->first();
    }

    protected function findPageBySlug(int $schoolId, string $slug): ?SchoolPage
    {
        return SchoolPage::where('school_id', $schoolId)
            ->where('slug', $slug)
            ->where('status', 'published')
            ->first();
    }

    protected function getPublishedSections(int $pageId)
    {
        $sections = SchoolPageSection::where('school_page_id', $pageId)
            ->orderBy('sort_order')
            ->get();

        // Hydrate gallery section with actual gallery items
        return $sections->map(function ($section) {
            if ($section->section_type === 'gallery') {
                $section->gallery_images = $this->getGalleryImages($section->school_id);
            }
            if ($section->section_type === 'testimonials') {
                $section->testimonial_items = $this->getPublishedTestimonials($section->school_id);
            }
            if ($section->section_type === 'stats') {
                $section->stats = $this->generateStats($section->school_id, $section->config ?? []);
            }
            if ($section->section_type === 'news') {
                $section->news_posts = $this->getNewsPosts($section->school_id);
            }
            return $section;
        });
    }

    protected function getGalleryImages(int $schoolId): array
    {
        return SchoolGallery::where('school_id', $schoolId)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    protected function getPublishedTestimonials(int $schoolId): array
    {
        return SchoolTestimonial::where('school_id', $schoolId)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get()
            ->toArray();
    }

    protected function generateStats(int $schoolId, array $config): array
    {
        $stats = $config['stats'] ?? [];

        if (empty($stats)) {
            $studentCount = \App\Models\Student::where('school_id', $schoolId)->count();
            $staffCount = \App\Models\Staff::where('school_id', $schoolId)->count();  // May not exist; fallback
            $stats = [
                ['label' => 'Siswa', 'value' => $studentCount, 'icon' => 'graduation-cap'],
                ['label' => 'Guru & Staff', 'value' => $staffCount > 0 ? $staffCount : 20, 'icon' => 'users'],
                ['label' => 'Tahun Berdiri', 'value' => date('Y') - ($config['founded_year'] ?? 2000), 'icon' => 'calendar'],
                ['label' => 'Jurusan', 'value' => $config['majors_count'] ?? 5, 'icon' => 'book-open'],
            ];
        }

        return $stats;
    }

    protected function getNewsPosts(int $schoolId): array
    {
        if (class_exists(\App\Models\BlogPost::class)) {
            return \App\Models\BlogPost::where('school_id', $schoolId)
                ->where('is_published', true)
                ->whereNotNull('published_at')
                ->where('published_at', '<=', now())
                ->orderBy('published_at', 'desc')
                ->limit(3)
                ->get()
                ->toArray();
        }
        return [];
    }
}
