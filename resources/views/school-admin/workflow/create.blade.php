@extends('layouts.school-admin')
@section('title', 'Ajukan Permintaan')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="max-w-2xl space-y-6">
    <div>
        <h1 class="page-title">Ajukan Permintaan Persetujuan</h1>
        <p class="text-sm text-[var(--color-text-secondary)] mt-1">Permintaan akan masuk ke antrian persetujuan dan bisa diproses oleh pemberi persetujuan.</p>
    </div>

    <form method="POST" action="{{ route('admin.workflow.store') }}" class="card card-pad space-y-5">
        @csrf
        <x-ui.select name="type" label="Tipe Permintaan" :required="true" :error="$errors->first('type')">
            <option value="">— Pilih tipe —</option>
            @foreach($types as $key => $label)
                <option value="{{ $key }}" @selected(old('type') === $key)>{{ $label }}</option>
            @endforeach
        </x-ui.select>

        <x-ui.input name="title" label="Judul" :required="true" :value="old('title')" :error="$errors->first('title')" hint="Ringkasan singkat permintaan Anda." />

        <x-ui.textarea name="description" label="Deskripsi" :value="old('description')" :error="$errors->first('description')" rows="4" hint="Detail yang diperlukan pemberi persetujuan untuk memutuskan." />

        <div class="pt-4 border-t border-[var(--color-border)] flex justify-end gap-2">
            <a href="{{ route('admin.workflow.index') }}" class="btn btn-ghost">Batal</a>
            <x-ui.button type="submit">Ajukan</x-ui.button>
        </div>
    </form>
</div>

@endsection
