@extends('layouts.school-admin')
@section('title', 'Edit Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.students.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>

<div class="mb-7 max-w-3xl">
    <div class="elite-kicker mb-2">Discipulus #{{ $student->admission_no }}</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Edit: {{ $student->user?->name }}</h1>
    <div class="elite-rule"></div>
</div>

<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.students.update', $student) }}">
        @include('school-admin.students._form')
    </form>
</div>

@endsection
