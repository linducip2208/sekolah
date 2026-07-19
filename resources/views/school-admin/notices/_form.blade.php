@php
    $notice = $notice ?? null;
    $isEdit = $notice !== null;
    $selectedRoles = old('target_roles', $notice?->target_roles ?? []);
    $selectedSections = old('target_class_sections', $notice?->target_class_sections ?? []);
@endphp

@if($errors->any())
    <div class="mb-5 px-5 py-3 bg-red-50 border-l-4 border-red-700">
        <ul class="list-disc list-inside font-serif text-sm text-red-800">
            @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
        </ul>
    </div>
@endif

<div class="bg-white border border-rule p-7 space-y-5">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Judul</label>
        <input name="title" required maxlength="255" value="{{ old('title', $notice?->title) }}"
               class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
    </div>
    <div>
        <label class="elite-kicker text-[.6rem] block mb-1">Isi Pengumuman</label>
        <textarea name="content" rows="6" required maxlength="10000"
                  class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">{{ old('content', $notice?->content) }}</textarea>
    </div>

    <div>
        <label class="elite-kicker text-[.6rem] block mb-2">Target Audience</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach($roles as $r)
                <label class="flex items-center gap-2 px-3 py-2 border-2 border-rule cursor-pointer">
                    <input type="checkbox" name="target_roles[]" value="{{ $r }}"
                           @checked(in_array($r, $selectedRoles ?? []))>
                    <span class="text-sm font-serif">{{ ucfirst($r) }}</span>
                </label>
            @endforeach
        </div>
    </div>

    @if($classSections->count() > 0)
    <div>
        <label class="elite-kicker text-[.6rem] block mb-2">Spesifik Rombel (opsional)</label>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
            @foreach($classSections as $cs)
                <label class="flex items-center gap-2 px-3 py-2 border-2 border-rule cursor-pointer text-xs">
                    <input type="checkbox" name="target_class_sections[]" value="{{ $cs->id }}"
                           @checked(in_array($cs->id, $selectedSections ?? []))>
                    <span class="font-serif">{{ $cs->classRoom?->name }} {{ $cs->section?->name }}</span>
                </label>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid md:grid-cols-2 gap-4">
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Publish Saat</label>
            <input type="datetime-local" name="publish_at"
                   value="{{ old('publish_at', $notice?->publish_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
        <div>
            <label class="elite-kicker text-[.6rem] block mb-1">Expire Saat</label>
            <input type="datetime-local" name="expire_at"
                   value="{{ old('expire_at', $notice?->expire_at?->format('Y-m-d\TH:i')) }}"
                   class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
        </div>
    </div>

    <label class="flex items-center gap-2">
        <input type="checkbox" name="is_published" value="1" @checked(old('is_published', $notice?->is_published ?? true))>
        <span class="text-sm">Publikasikan langsung</span>
    </label>

    <div class="pt-5 border-t border-rule flex gap-3">
        <button class="btn-elite">{{ $isEdit ? 'Simpan' : 'Publikasikan' }}</button>
        <a href="{{ route('admin.notices.index') }}" class="btn-elite-ghost">Batal</a>
    </div>
</div>
