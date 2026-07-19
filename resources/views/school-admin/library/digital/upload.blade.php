@extends('layouts.school-admin')
@section('title', 'Upload Buku Digital')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7">
    <div class="flex justify-between items-end">
        <div>
            <div class="elite-kicker mb-2">Bibliotheca Digitalis</div>
            <h1 class="elite-h1 text-3xl ink-primary mb-2">Buku Digital</h1>
            <div class="elite-rule"></div>
            <p class="font-serif text-sm text-gray-600 mt-3">Unggah buku digital (PDF/EPUB) untuk dibaca siswa & guru secara online.</p>
        </div>
        <div class="flex gap-3">
            <a href="{{ route('admin.library.digital.stats') }}" class="btn-elite-ghost">Statistik</a>
            <a href="{{ route('admin.library.books.index') }}" class="btn-elite-ghost">Katalog Fisik</a>
        </div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Unggah Buku Digital</h3>
            @if($errors->any())<div class="mb-3 px-3 py-2 bg-red-50 text-xs text-red-800">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ route('admin.library.digital.store') }}" enctype="multipart/form-data" class="space-y-3">
                @csrf
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Judul Buku</label>
                    <input name="title" required maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Penulis</label>
                    <input name="author" maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Kategori</label>
                    <select name="book_category_id" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih —</option>
                        @foreach(\App\Models\Facilities\BookCategory::where('school_id', auth()->user()->school_id)->orderBy('name')->get() as $c)
                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Penerbit</label>
                    <input name="publisher" maxlength="255" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">File Buku (PDF / EPUB, max 100MB)</label>
                    <input type="file" name="digital_file" required accept=".pdf,.epub" class="w-full border-2 border-rule px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Halaman Pratinjau (default 10)</label>
                    <input type="number" name="preview_pages" value="10" min="0" max="100" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_downloadable" value="0">
                    <input type="checkbox" name="is_downloadable" value="1" class="w-4 h-4">
                    <span class="font-serif text-xs text-gray-700">Izinkan download file</span>
                </label>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea>
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Unggah Buku Digital</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white">
                    <tr>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Judul</th>
                        <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">File</th>
                        <th class="text-center px-3 py-3 elite-kicker text-[.6rem]">Dibaca</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($digitalBooks as $b)
                        <tr class="border-t border-rule hover:bg-gray-50">
                            <td class="px-3 py-3">
                                <div class="font-serif font-semibold">{{ $b->title }}</div>
                                <div class="text-xs text-gray-500">{{ $b->author ?? '—' }} · {{ strtoupper($b->file_type) }} {{ $b->file_size ? round($b->file_size/1024/1024,1).' MB' : '' }}</div>
                            </td>
                            <td class="px-3 py-3 text-xs">
                                <span class="px-2 py-0.5 {{ $b->is_downloadable ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                    {{ $b->is_downloadable ? 'Bisa Download' : 'Baca Online' }}
                                </span>
                            </td>
                            <td class="px-3 py-3 text-center font-mono text-xs">{{ $b->read_count }}x</td>
                            <td class="px-3 py-3 text-right">
                                <button onclick="showIssueModal({{ $b->id }}, '{{ e($b->title) }}')" class="text-xs ink-accent hover:underline mr-3">Beri Akses</button>
                                <form method="POST" action="{{ route('admin.library.digital.delete', $b) }}" class="inline" onsubmit="return confirm('Hapus buku digital?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs text-red-700 hover:underline">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="p-10 text-center text-gray-500 italic font-serif">Belum ada buku digital. Unggah buku pertama Anda.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- Issue Modal --}}
<div x-data="{ open: false, bookId: null, bookTitle: '' }"
     x-show="open" x-cloak
     class="fixed inset-0 z-50 flex items-center justify-center" style="background: rgba(11,29,58,.75);">
    <div @click.outside="open = false" class="bg-white w-full max-w-lg shadow-2xl border border-rule p-6">
        <h3 class="elite-h3 text-lg ink-primary mb-1">Beri Akses Digital</h3>
        <p class="font-serif text-sm text-gray-500 mb-4" x-text="bookTitle"></p>
        <form method="POST" action="{{ route('admin.library.digital.issue') }}">
            @csrf
            <input type="hidden" name="book_id" :value="bookId">
            <div class="space-y-3">
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Siswa</label>
                    <select name="student_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih siswa —</option>
                        @foreach(\App\Models\Academic\Student::where('school_id', auth()->user()->school_id)->with('user')->get() as $s)
                            <option value="{{ $s->user_id }}">{{ $s->user->name }} ({{ $s->admission_no }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Staff / Guru</label>
                    <select name="staff_id" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— pilih staff —</option>
                        @foreach(\App\Models\User::where('school_id', auth()->user()->school_id)->where('is_active', true)->orderBy('name')->get(['id','name']) as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="elite-kicker text-[.6rem] block mb-1">Durasi Akses (hari)</label>
                    <input type="number" name="duration_days" value="30" min="1" max="365" class="w-full border-2 border-rule px-3 py-2 font-mono text-sm">
                </div>
                <div class="flex gap-2 pt-2">
                    <button type="button" @click="open = false" class="flex-1 btn-elite-ghost text-xs">Batal</button>
                    <button type="submit" class="flex-1 btn-elite text-xs">Beri Akses</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function showIssueModal(bookId, bookTitle) {
    const root = document.querySelector('[x-data]');
    if (root && root._x_dataStack[0]) {
        const data = root._x_dataStack[0];
        data.bookId = bookId;
        data.bookTitle = bookTitle;
        data.open = true;
    }
}
</script>

@endsection
