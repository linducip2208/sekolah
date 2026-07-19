@extends('layouts.school-admin')
@section('title', 'Lesson Plan / RPP')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <h2 class="text-xl font-bold">Lesson Plan / RPP</h2>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Pending Approval</div><div class="text-3xl font-bold text-blue-600">{{ $stats['submitted'] }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Approved</div><div class="text-3xl font-bold text-green-600">{{ $stats['approved'] }}</div></div>
        <div class="bg-white rounded-lg p-5 shadow"><div class="text-xs text-gray-500">Executed</div><div class="text-3xl font-bold">{{ $stats['completed'] }}</div></div>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">RPP Terbaru</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr><th class="px-4 py-2">Tanggal</th><th class="px-4 py-2">Judul</th><th class="px-4 py-2">Guru</th><th class="px-4 py-2">Kurikulum</th><th class="px-4 py-2">Status</th></tr>
            </thead>
            <tbody class="divide-y">
                @forelse($plans as $p)
                    <tr>
                        <td class="px-4 py-2 text-xs">{{ $p->lesson_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 font-medium">{{ $p->title }}</td>
                        <td class="px-4 py-2 text-xs">User #{{ $p->teacher_id }}</td>
                        <td class="px-4 py-2"><span class="px-2 py-0.5 bg-purple-50 text-purple-800 rounded text-xs">{{ $p->curriculum_type }}</span></td>
                        <td class="px-4 py-2">
                            @php $color = match($p->status){'approved'=>'green','submitted'=>'blue','rejected'=>'red','completed'=>'gray',default=>'yellow'};@endphp
                            <span class="px-2 py-0.5 bg-{{ $color }}-100 text-{{ $color }}-800 rounded text-xs">{{ $p->status }}</span>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">Belum ada RPP</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
