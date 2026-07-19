@extends('layouts.school-admin')
@section('title', 'Edit Pengumuman')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.notices.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>

<div class="mb-7 max-w-3xl">
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Edit: {{ $notice->title }}</h1>
    <div class="elite-rule"></div>
</div>

<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.notices.update', $notice) }}">
        @include('school-admin.notices._form')
    </form>
</div>
@endsection
