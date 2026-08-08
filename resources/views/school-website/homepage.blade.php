<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $page->title ?? ($branding['display_name'] ?? 'Website Sekolah') }}</title>
    @if(!empty($page->meta_description))
        <meta name="description" content="{{ $page->meta_description }}">
    @endif
    @if(!empty($branding['logos']['favicon'] ?? null))
        <link rel="icon" href="{{ $branding['logos']['favicon'] }}">
    @endif
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800|playfair-display:400,500,600,700,800" rel="stylesheet">
    <style>
        :root {
            --c-primary: {{ $branding['colors']['primary'] ?? '#2563EB' }};
            --c-accent: {{ $branding['colors']['accent'] ?? '#0EA5E9' }};
        }
        body { font-family: 'Inter', system-ui, -apple-system, sans-serif; }
        .font-display { font-family: 'Playfair Display', Georgia, serif; }
    </style>
</head>
<body class="bg-white text-gray-900 antialiased">

    {{-- Navbar --}}
    <nav class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b shadow-sm">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center gap-3">
                @if(!empty($branding['logos']['primary'] ?? null))
                    <img src="{{ $branding['logos']['primary'] }}" alt="Logo" class="h-10 w-auto">
                @endif
                <div>
                    <div class="font-display font-bold text-xl" style="color: var(--c-primary)">{{ $branding['display_name'] ?? 'Sekolah' }}</div>
                    @if(!empty($branding['tagline'] ?? ''))
                        <div class="text-xs text-gray-500">{{ $branding['tagline'] }}</div>
                    @endif
                </div>
            </div>
            <div class="flex items-center gap-4 text-sm">
                <a href="{{ url('/s/' . $school->subdomain) }}" class="text-gray-700 hover:text-blue-600 font-medium">Beranda</a>
                @php
                    $otherPages = \App\Models\Website\SchoolPage::where('school_id', $school->id)
                        ->where('status', 'published')
                        ->where('id', '!=', $page->id)
                        ->orderBy('sort_order')
                        ->take(4)
                        ->get();
                @endphp
                @foreach($otherPages as $otherPage)
                    <a href="{{ url('/s/' . $school->subdomain . '/' . $otherPage->slug) }}" class="text-gray-700 hover:text-blue-600">{{ $otherPage->title }}</a>
                @endforeach
            </div>
        </div>
    </nav>

    {{-- Render sections --}}
    @foreach($sections as $section)
        @php $widget = 'school-website.widgets.' . $section->section_type; @endphp
        @if(view()->exists($widget))
            @include($widget, ['section' => $section, 'branding' => $branding, 'school' => $school])
        @endif
    @endforeach

    {{-- Footer --}}
    <footer class="bg-gray-900 text-gray-400 py-12">
        <div class="container mx-auto px-4 text-center">
            <p class="text-lg font-display font-bold text-white mb-2">{{ $branding['display_name'] ?? 'Sekolah' }}</p>
            @if(!empty($branding['tagline'] ?? ''))
                <p class="mb-4">{{ $branding['tagline'] }}</p>
            @endif
            <p class="text-sm">Powered by Sikad Pro &copy; {{ date('Y') }}</p>
        </div>
    </footer>

</body>
</html>
