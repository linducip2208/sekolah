@extends('layouts.school-admin')
@section('title', 'Learning Analytics')
@section('sidebar')@include('school-admin.partials.sidebar-extended')@endsection
@section('content')
<div class="space-y-6">
    <div class="flex justify-between">
        <h2 class="text-xl font-bold">Learning Analytics</h2>
        <form method="POST" action="#" class="inline">@csrf
            <button class="btn-brand text-sm">Recompute Risk Scores</button>
        </form>
    </div>

    <div class="grid grid-cols-4 gap-4">
        @php $levels = ['low'=>'green','medium'=>'yellow','high'=>'orange','critical'=>'red'];@endphp
        @foreach($levels as $level => $color)
            <div class="bg-white rounded-lg p-5 shadow">
                <div class="text-xs text-gray-500">{{ ucfirst($level) }} Risk</div>
                <div class="text-3xl font-bold text-{{ $color }}-600">{{ $distribution[$level] ?? 0 }}</div>
            </div>
        @endforeach
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="px-4 py-3 border-b font-bold">⚠️ Siswa At-Risk (High & Critical)</div>
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-left text-gray-600">
                <tr>
                    <th class="px-4 py-2">Siswa</th>
                    <th class="px-4 py-2">Attendance</th>
                    <th class="px-4 py-2">Academic</th>
                    <th class="px-4 py-2">Behavior</th>
                    <th class="px-4 py-2">Engagement</th>
                    <th class="px-4 py-2">Risk Score</th>
                    <th class="px-4 py-2">Faktor</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($atRisk as $r)
                    <tr>
                        <td class="px-4 py-2">Student #{{ $r->student_id }}</td>
                        <td class="px-4 py-2 text-xs">{{ $r->attendance_score }}</td>
                        <td class="px-4 py-2 text-xs">{{ $r->academic_score }}</td>
                        <td class="px-4 py-2 text-xs">{{ $r->behavior_score }}</td>
                        <td class="px-4 py-2 text-xs">{{ $r->engagement_score }}</td>
                        <td class="px-4 py-2 font-bold">
                            @php $col = $r->risk_level === 'critical' ? 'red' : ($r->risk_level === 'high' ? 'orange' : 'yellow');@endphp
                            <span class="text-{{ $col }}-600">{{ $r->overall_risk }}</span>
                            <span class="ml-1 px-2 py-0.5 bg-{{ $col }}-100 rounded text-xs">{{ $r->risk_level }}</span>
                        </td>
                        <td class="px-4 py-2 text-xs">
                            @foreach((array)$r->top_risk_factors as $f)
                                <span class="inline-block px-2 py-0.5 bg-gray-100 rounded mr-1 mb-1">{{ $f }}</span>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Tidak ada siswa at-risk hari ini. 🎉</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
