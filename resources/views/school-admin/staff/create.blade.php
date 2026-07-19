@extends('layouts.school-admin')
@section('title', 'Tambah Staff')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<a href="{{ route('admin.staff.index') }}" class="elite-kicker text-xs ink-secondary hover:ink-accent mb-4 inline-block">← Kembali</a>

<div class="mb-7 max-w-3xl">
    <div class="elite-kicker mb-2">Novus Officialis</div>
    <h1 class="elite-h1 text-3xl ink-primary mb-2">Tambah Staff Baru</h1>
    <div class="elite-rule"></div>
</div>

<div class="max-w-3xl">
    <form method="POST" action="{{ route('admin.staff.store') }}">
        @include('school-admin.staff._form')
    </form>
</div>
@endsection
