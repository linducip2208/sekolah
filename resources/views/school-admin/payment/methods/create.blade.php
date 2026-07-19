@extends('layouts.school-admin')
@section('title', 'Tambah Metode')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-4xl">
    <h2 class="text-xl font-bold mb-1">Tambah Metode Pembayaran</h2>
    <form method="POST" action="{{ route('admin.payment.methods.store') }}" class="mt-5">
        @csrf
        @include('school-admin.payment.methods._form')
    </form>
</div>
@endsection
