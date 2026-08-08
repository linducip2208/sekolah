@extends('elite.layout')

@section('title', $meta['title'] ?? 'Sikad Pro')
@section('description', $meta['description'] ?? '')

@push('jsonld')
    @if(!empty($jsonLd))
        <script type="application/ld+json">{!! json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @endif
@endpush

@section('header')
    @include('elite.partials.header')
@endsection

@section('content')
    <div class="paper">
        @yield('seo_content')
    </div>
@endsection
