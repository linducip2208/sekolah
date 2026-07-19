@extends('layouts.school-admin')
@section('title', 'Aturan Booking: ' . $room->name)
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')
<div class="max-w-5xl space-y-6" x-data="{ rules: @json($rules) }">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold">Aturan Booking: {{ $room->name }}</h2>
            <p class="text-sm text-gray-600">Tentukan aturan untuk booking ruangan ini.</p>
        </div>
        <a href="{{ route('admin.facilities.rooms.index') }}" class="text-sm text-blue-600 hover:underline">← Kembali</a>
    </div>

    <div class="bg-white rounded-lg shadow p-6">
        <form method="POST" action="{{ route('admin.facilities.rooms.rules.save', $room) }}">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Durasi Maksimal (jam)</label>
                    <input type="number" name="rules[0][rule_type]" value="max_duration_hours" hidden>
                    <input type="text" name="rules[0][rule_value]" class="w-full border rounded-lg px-3 py-2 text-sm"
                        placeholder="2" value="{{ collect($rules)->where('rule_type','max_duration_hours')->first()['rule_value'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Maks. Booking di Muka (hari)</label>
                    <input type="number" name="rules[1][rule_type]" value="max_advance_days" hidden>
                    <input type="number" name="rules[1][rule_value]" class="w-full border rounded-lg px-3 py-2 text-sm"
                        placeholder="30" value="{{ collect($rules)->where('rule_type','max_advance_days')->first()['rule_value'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Jeda Minimal Antar Booking (menit)</label>
                    <input type="number" name="rules[2][rule_type]" value="min_gap_minutes" hidden>
                    <input type="number" name="rules[2][rule_value]" class="w-full border rounded-lg px-3 py-2 text-sm"
                        placeholder="15" value="{{ collect($rules)->where('rule_type','min_gap_minutes')->first()['rule_value'] ?? '' }}">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Role yang Diizinkan (pisahkan dengan koma)</label>
                    <input type="text" name="rules[3][rule_type]" value="allowed_roles" hidden>
                    <input type="text" name="rules[3][rule_value]" class="w-full border rounded-lg px-3 py-2 text-sm"
                        placeholder="admin,teacher" value="{{ collect($rules)->where('rule_type','allowed_roles')->first()['rule_value'] ?? '' }}">
                </div>
            </div>
            <div class="flex justify-end mt-6 pt-4 border-t">
                <button type="submit" class="btn-elite px-4 py-2 rounded-lg text-sm font-semibold">Simpan Aturan</button>
            </div>
        </form>
    </div>
</div>
@endsection
