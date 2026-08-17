<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Verifikasi Rapor — {{ config('app.name') }}</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-2xl bg-white rounded-2xl shadow-lg border border-slate-200 overflow-hidden">
    <div class="bg-gradient-to-r from-blue-700 to-indigo-700 text-white px-6 py-5">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 rounded-full w-10 h-10 flex items-center justify-center">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
            </div>
            <div>
                <div class="font-bold text-lg">Rapor Terverifikasi</div>
                <div class="text-xs text-blue-100">Dokumen resmi — {{ config('app.name') }}</div>
            </div>
        </div>
    </div>

    <div class="px-6 py-6">
        <div class="grid sm:grid-cols-2 gap-4 mb-6">
            <div>
                <div class="text-xs text-slate-400 uppercase tracking-wide">Nama Siswa</div>
                <div class="font-semibold text-slate-800">{{ $card->student?->user?->name ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-400 uppercase tracking-wide">NIS</div>
                <div class="font-semibold text-slate-800 font-mono">{{ $card->student?->admission_no ?? '—' }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-400 uppercase tracking-wide">Kelas</div>
                <div class="font-semibold text-slate-800">{{ $card->student?->classSection?->classRoom?->name }} {{ $card->student?->classSection?->section?->name }}</div>
            </div>
            <div>
                <div class="text-xs text-slate-400 uppercase tracking-wide">Semester</div>
                <div class="font-semibold text-slate-800">{{ $card->semester?->name ?? '—' }}</div>
            </div>
        </div>

        <div class="flex items-center justify-center gap-4 mb-6">
            <div class="text-center bg-slate-50 rounded-xl px-6 py-3">
                <div class="text-xs text-slate-400">Rata-rata</div>
                <div class="font-bold text-2xl text-slate-800">{{ $card->total_percentage }}%</div>
            </div>
            <div class="text-center bg-blue-50 rounded-xl px-6 py-3">
                <div class="text-xs text-slate-400">Grade</div>
                <div class="font-bold text-2xl text-blue-700">{{ $card->overall_grade ?? '—' }}</div>
            </div>
            @if($card->gpa)<div class="text-center bg-green-50 rounded-xl px-6 py-3">
                <div class="text-xs text-slate-400">GPA</div>
                <div class="font-bold text-2xl text-green-700">{{ $card->gpa }}</div>
            </div>@endif
        </div>

        <div class="border border-slate-200 rounded-xl overflow-hidden mb-6">
            <div class="bg-slate-50 px-4 py-2 text-xs font-semibold text-slate-500 uppercase">Nilai Mata Pelajaran</div>
            <table class="w-full text-sm">
                <tbody>
                    @forelse($marks as $m)
                    <tr class="border-t border-slate-100">
                        <td class="px-4 py-2 text-slate-700">{{ $m->subject?->name ?? '—' }}</td>
                        <td class="px-4 py-2 text-right font-mono text-slate-600">{{ $m->obtained_marks }}/{{ $m->total_marks }}</td>
                        <td class="px-4 py-2 text-right font-bold text-slate-800 w-16">{{ $m->grade ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="3" class="px-4 py-6 text-center text-slate-400 italic">Belum ada nilai.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex items-center gap-3 bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-800">
            <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
            <span>Dokumen ini sah dan diterbitkan oleh sistem {{ config('app.name') }}.</span>
        </div>
    </div>
</div>
</body>
</html>
