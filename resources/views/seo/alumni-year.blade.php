@extends('seo.layout')
@section('content')
<article class="prose max-w-none">
    <h1 class="text-3xl font-bold mb-4">Alumni {{ $school->name }} Angkatan {{ $year }}</h1>
    <p class="text-gray-700">
        Direktori alumni {{ $school->name }} angkatan {{ $year }}.
        Cari teman lama, mentor, atau jaringan profesional dari almamater Anda.
    </p>

    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mt-8 not-prose">
        @foreach($alumni as $a)
            <div class="border rounded p-4">
                <h3 class="font-semibold">Alumni {{ $year }}</h3>
                @if($a->current_position)
                    <p class="text-sm">{{ $a->current_position }}</p>
                @endif
                @if($a->current_company)
                    <p class="text-xs text-gray-600">{{ $a->current_company }}</p>
                @endif
                @if($a->city)
                    <p class="text-xs text-gray-500 mt-1">📍 {{ $a->city }}</p>
                @endif
                @if($a->willing_to_mentor)
                    <span class="inline-block mt-2 px-2 py-0.5 bg-green-100 text-green-800 text-xs rounded">Open to mentor</span>
                @endif
            </div>
        @endforeach
    </div>

    <div class="mt-6 not-prose">
        {{ $alumni->links() }}
    </div>
</article>
@endsection
