@extends('layouts.school-admin')
@section('title', 'Tambah Siswa')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.students.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>

<div class="mb-7 max-w-3xl">
    <div class="elite-kicker mb-2">Novus Discipulus</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Tambah Siswa Baru</h1>
    <div class="elite-rule"></div>
</div>

<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.students.store') }}">
        @include('school-admin.students._form')
    </form>
</div>

@endsection
