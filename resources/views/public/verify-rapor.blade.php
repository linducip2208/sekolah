<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Rapor — {{ config('app.name') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800" rel="stylesheet" />
    <style>body { font-family: 'Plus Jakarta Sans', sans-serif; }</style>
</head>
<body class="bg-gray-50 min-h-screen flex items-start justify-center py-8 px-4">
    <div class="w-full max-w-2xl space-y-6">
        {{-- Header --}}
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-green-100 mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Verifikasi Rapor</h1>
            <p class="text-sm text-gray-500 mt-1">{{ config('app.name') }}</p>
        </div>

        {{-- Validity Badge --}}
        <div class="rounded-xl p-4 text-center {{ $card->is_published ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
            @if($card->is_published)
                <div class="flex items-center justify-center gap-2 text-green-700 font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    Rapor Valid & Terpublikasi
                </div>
                <p class="text-xs text-green-600 mt-1">Dokumen ini diterbitkan secara resmi oleh sekolah.</p>
            @else
                <div class="text-red-700 font-bold">Rapor Tidak Valid</div>
            @endif
        </div>

        {{-- Student Info --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6 space-y-4">
            <h2 class="font-bold text-gray-900 text-lg">Data Siswa</h2>
            <div class="grid grid-cols-2 gap-3 text-sm">
                <div>
                    <div class="text-gray-500">Nama</div>
                    <div class="font-semibold text-gray-900">{{ $card->student?->user?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">NIS</div>
                    <div class="font-semibold text-gray-900">{{ $card->student?->student_id ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Kelas</div>
                    <div class="font-semibold text-gray-900">{{ $card->student?->classSection?->classRoom?->name ?? '-' }} {{ $card->student?->classSection?->section?->name ?? '' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Semester</div>
                    <div class="font-semibold text-gray-900">{{ $card->semester?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">GPA</div>
                    <div class="font-bold text-indigo-600 text-lg">{{ $card->gpa ? number_format($card->gpa, 2) : '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-500">Ranking</div>
                    <div class="font-semibold text-gray-900">{{ $card->rank ? '#'.$card->rank : '-' }}</div>
                </div>
            </div>
        </div>

        {{-- Marks --}}
        @if($marks->isNotEmpty())
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
            <h2 class="font-bold text-gray-900 text-lg mb-4">Daftar Nilai</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-200">
                        <th class="text-left py-2 text-gray-500 font-medium">Mata Pelajaran</th>
                        <th class="text-center py-2 text-gray-500 font-medium">Nilai</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($marks as $mark)
                    <tr class="border-b border-gray-100 last:border-0">
                        <td class="py-2 text-gray-900">{{ $mark->subject?->name ?? '-' }}</td>
                        <td class="py-2 text-center font-semibold text-gray-900">{{ $mark->score ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

        {{-- Footer --}}
        <div class="text-center text-xs text-gray-400">
            <p>Verifikasi pada {{ now()->format('d M Y H:i') }}</p>
            <p class="mt-1">Token: {{ $token }}</p>
        </div>
    </div>
</body>
</html>
