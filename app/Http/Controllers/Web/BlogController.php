<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class BlogController extends Controller
{
    public function index(Request $request): View
    {
        $posts = BlogPost::published()
            ->with(['category', 'author'])
            ->latest('published_at')
            ->paginate(12);

        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        $recentPosts = BlogPost::published()
            ->with('category')
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('blog.index', [
            'posts'      => $posts,
            'categories' => $categories,
            'recentPosts' => $recentPosts,
            'meta' => [
                'title'       => 'Blog — Informasi & Wawasan Pendidikan',
                'description' => 'Artikel terbaru seputar pendidikan, manajemen sekolah, tips mengajar, teknologi pendidikan, dan wawasan untuk kepala sekolah, guru, dan orang tua.',
            ],
        ]);
    }

    public function show(string $slug): View
    {
        $post = BlogPost::published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        $relatedPosts = BlogPost::published()
            ->with('category')
            ->where('id', '!=', $post->id)
            ->where('category_id', $post->category_id)
            ->latest('published_at')
            ->take(3)
            ->get();

        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        $recentPosts = BlogPost::published()
            ->with('category')
            ->latest('published_at')
            ->take(5)
            ->get();

        $jsonLd = [
            '@context'    => 'https://schema.org',
            '@type'       => 'Article',
            'headline'    => $post->title,
            'description' => $post->meta_description ?? $post->excerpt,
            'image'       => $post->featured_image ? asset($post->featured_image) : null,
            'datePublished' => $post->published_at?->toIso8601String(),
            'dateModified'  => $post->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Person',
                'name'  => $post->author?->name ?? 'Sikad Pro',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name'  => config('app.name', 'Sikad Pro'),
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id'   => route('blog.show', $post->slug),
            ],
        ];

        return view('blog.show', [
            'post'         => $post,
            'relatedPosts' => $relatedPosts,
            'categories'   => $categories,
            'recentPosts'  => $recentPosts,
            'jsonLd'       => $jsonLd,
            'meta' => [
                'title'       => $post->meta_title ?: $post->title,
                'description' => $post->meta_description ?: $post->excerpt,
            ],
        ]);
    }

    public function category(string $slug): View
    {
        $category = BlogCategory::where('slug', $slug)->firstOrFail();

        $posts = BlogPost::published()
            ->with(['category', 'author'])
            ->byCategory($slug)
            ->latest('published_at')
            ->paginate(12);

        $categories = BlogCategory::withCount(['posts' => fn ($q) => $q->published()])
            ->orderBy('name')
            ->get();

        $recentPosts = BlogPost::published()
            ->with('category')
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('blog.category', [
            'category'    => $category,
            'posts'       => $posts,
            'categories'  => $categories,
            'recentPosts' => $recentPosts,
            'meta' => [
                'title'       => "Kategori: {$category->name} — Blog Sikad Pro",
                'description' => "Artikel dalam kategori {$category->name} — " . ($category->description ?: 'Kumpulan artikel pendidikan.'),
            ],
        ]);
    }

    public function feed(): Response
    {
        $posts = BlogPost::published()
            ->with('author')
            ->latest('published_at')
            ->take(20)
            ->get();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom" xmlns:content="http://purl.org/rss/1.0/modules/content/">' . "\n";
        $xml .= "  <channel>\n";
        $xml .= '    <title>' . e(config('app.name', 'Sikad Pro')) . " Blog</title>\n";
        $xml .= '    <link>' . e(route('blog.index')) . "</link>\n";
        $xml .= '    <description>Artikel terbaru seputar pendidikan dan manajemen sekolah</description>' . "\n";
        $xml .= '    <language>id-ID</language>' . "\n";
        $xml .= '    <lastBuildDate>' . ($posts->first()?->published_at?->toRssString() ?? now()->toRssString()) . "</lastBuildDate>\n";
        $xml .= '    <atom:link href="' . e(route('blog.feed')) . '" rel="self" type="application/rss+xml"/>' . "\n";

        foreach ($posts as $post) {
            $xml .= "    <item>\n";
            $xml .= '      <title>' . e($post->title) . "</title>\n";
            $xml .= '      <link>' . e(route('blog.show', $post->slug)) . "</link>\n";
            $xml .= '      <guid isPermaLink="true">' . e(route('blog.show', $post->slug)) . "</guid>\n";
            $xml .= '      <description>' . e($post->excerpt ?: strip_tags(html_entity_decode(mb_substr($post->content, 0, 300)))) . "</description>\n";
            $xml .= '      <content:encoded><![CDATA[' . $post->content . "]]></content:encoded>\n";
            $xml .= '      <author>' . e($post->author?->email ?? 'no-reply@sikadpro.app') . ' (' . e($post->author?->name ?? 'Sikad Pro') . ")</author>\n";
            if ($post->category) {
                $xml .= '      <category>' . e($post->category->name) . "</category>\n";
            }
            $xml .= '      <pubDate>' . $post->published_at->toRssString() . "</pubDate>\n";
            if ($post->featured_image) {
                $xml .= '      <enclosure url="' . e(asset($post->featured_image)) . '" type="image/' . pathinfo($post->featured_image, PATHINFO_EXTENSION) . '"/>' . "\n";
            }
            $xml .= "    </item>\n";
        }

        $xml .= "  </channel>\n";
        $xml .= '</rss>';

        return response($xml)->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }
}
