@extends('layouts.school-admin')
@section('title', 'Tambah Provider')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-4xl">
    <h2 class="text-xl font-bold mb-1">Tambah Provider Pembayaran</h2>
    <p class="text-sm text-gray-600 mb-5">Pilih format API yang sesuai. Tidak ada nama vendor di-hardcode — Anda input semua kredensial sendiri.</p>
    <form method="POST" action="{{ route('admin.payment.providers.store') }}">
        @csrf
        @include('school-admin.payment.providers._form')
    </form>
</div>
@endsection
