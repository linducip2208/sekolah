@extends('layouts.school-admin')
@section('title', 'Edit Metode')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="bg-white rounded-lg shadow p-6 max-w-4xl">
    <h2 class="text-xl font-bold mb-1">Edit Metode — {{ $method->display_name }}</h2>
    <form method="POST" action="{{ route('admin.payment.methods.update', $method->id) }}" class="mt-5">
        @csrf @method('PUT')
        @include('school-admin.payment.methods._form', ['method' => $method])
    </form>
</div>
@endsection
