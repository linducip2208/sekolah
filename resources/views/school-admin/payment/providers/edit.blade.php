@extends('layouts.school-admin')
@section('title', 'Edit Provider')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-4xl">
    <h2 class="text-xl font-bold mb-1">Edit Provider — {{ $provider->name }}</h2>
    <p class="text-sm text-gray-600 mb-5">Kosongkan field kredensial jika tidak ingin mengubah.</p>
    <form method="POST" action="{{ route('admin.payment.providers.update', $provider->id) }}">
        @csrf @method('PUT')
        @include('school-admin.payment.providers._form', ['provider' => $provider])
    </form>
</div>
@endsection
