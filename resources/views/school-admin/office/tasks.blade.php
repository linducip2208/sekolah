@extends('layouts.school-admin')
@section('title', 'Tugas Staff')
@section('sidebar')@include('school-admin.partials.sidebar')@endsection
@section('content')

<div class="mb-7"><div class="elite-kicker mb-2">Kantor</div>
<h1 class="elite-h1 text-3xl ink-primary mb-2">Manajemen Tugas</h1>
<div class="elite-rule"></div></div>

<div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-6">
    <a href="{{ route('admin.office.tasks.index', ['status'=>'todo']) }}" class="bg-white border border-rule p-4 hover:shadow-md transition text-center {{ request('status')==='todo' ? 'ring-2 ring-blue-400' : '' }}">
        <div class="font-display text-2xl ink-primary font-bold">{{ $stats['todo'] }}</div>
        <div class="elite-kicker text-[.55rem] text-gray-500 mt-1">To Do</div>
    </a>
    <a href="{{ route('admin.office.tasks.index', ['status'=>'in_progress']) }}" class="bg-white border border-rule p-4 hover:shadow-md transition text-center {{ request('status')==='in_progress' ? 'ring-2 ring-amber-400' : '' }}">
        <div class="font-display text-2xl text-amber-600 font-bold">{{ $stats['in_progress'] }}</div>
        <div class="elite-kicker text-[.55rem] text-gray-500 mt-1">In Progress</div>
    </a>
    <a href="{{ route('admin.office.tasks.index', ['status'=>'done']) }}" class="bg-white border border-rule p-4 hover:shadow-md transition text-center {{ request('status')==='done' ? 'ring-2 ring-green-400' : '' }}">
        <div class="font-display text-2xl text-green-600 font-bold">{{ $stats['done'] }}</div>
        <div class="elite-kicker text-[.55rem] text-gray-500 mt-1">Done</div>
    </a>
    <a href="{{ route('admin.office.tasks.index', ['status'=>'overdue']) }}" class="bg-white border border-rule p-4 hover:shadow-md transition text-center {{ request('status')==='overdue' ? 'ring-2 ring-red-400' : '' }}">
        <div class="font-display text-2xl text-red-600 font-bold">{{ $stats['overdue'] }}</div>
        <div class="elite-kicker text-[.55rem] text-gray-500 mt-1">Overdue</div>
    </a>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <div class="lg:col-span-1">
        <div class="bg-white border border-rule p-6 sticky top-6">
            <h3 class="elite-h3 text-base ink-primary mb-3">Buat Tugas Baru</h3>
            <form method="POST" action="{{ route('admin.office.tasks.store') }}" class="space-y-3">
                @csrf
                <div><label class="elite-kicker text-[.6rem] block mb-1">Judul</label><input name="title" required maxlength="300" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Deskripsi</label><textarea name="description" rows="3" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm"></textarea></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Ditugaskan Ke</label>
                    <select name="assigned_to" required class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        <option value="">— Pilih —</option>
                        @foreach($staff as $s)<option value="{{ $s->id }}">{{ $s->name }}</option>@endforeach
                    </select>
                </div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Deadline</label><input type="date" name="due_date" class="w-full border-2 border-rule px-3 py-2 text-sm"></div>
                <div><label class="elite-kicker text-[.6rem] block mb-1">Prioritas</label>
                    <select name="priority" class="w-full border-2 border-rule px-3 py-2 font-serif text-sm">
                        @foreach(['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent'] as $v => $l)<option value="{{ $v }}">{{ $l }}</option>@endforeach
                    </select>
                </div>
                <button class="btn-elite w-full" style="padding:.6rem;font-size:.65rem;">Buat Tugas</button>
            </form>
        </div>
    </div>

    <div class="lg:col-span-2">
        <div class="bg-white border border-rule overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-[var(--c-primary)] text-white"><tr>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Tugas</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Ditugaskan Ke</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Deadline</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Prioritas</th>
                    <th class="text-left px-3 py-3 elite-kicker text-[.6rem]">Status</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @forelse($tasks as $t)
                <tr class="border-t border-rule hover:bg-gray-50">
                    <td class="px-3 py-3">
                        <div class="font-serif font-semibold ink-primary text-sm">{{ $t->title }}</div>
                        @if($t->description)<div class="text-xs text-gray-500 mt-0.5 max-w-[200px] truncate">{{ $t->description }}</div>@endif
                    </td>
                    <td class="px-3 py-3 text-xs">{{ $t->assignee?->name }}</td>
                    <td class="px-3 py-3 text-xs {{ $t->due_date && $t->due_date->isPast() && $t->status!=='done' ? 'text-red-600 font-semibold' : '' }}">
                        {{ $t->due_date ? $t->due_date->format('d/m/Y') : '—' }}
                    </td>
                    <td class="px-3 py-3">
                        @if($t->priority==='urgent')<span class="text-xs bg-red-100 text-red-700 px-2 py-0.5 rounded font-bold">Urgent</span>
                        @elseif($t->priority==='high')<span class="text-xs bg-orange-100 text-orange-700 px-2 py-0.5 rounded font-semibold">High</span>
                        @elseif($t->priority==='medium')<span class="text-xs bg-blue-100 text-blue-700 px-2 py-0.5 rounded font-semibold">Medium</span>
                        @else<span class="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded">Low</span>
                        @endif
                    </td>
                    <td class="px-3 py-3">
                        <form method="POST" action="{{ route('admin.office.tasks.update-status', $t) }}" class="inline">@csrf
                        <select name="status" onchange="this.form.submit()" class="text-xs border border-rule px-2 py-1">
                            @foreach(['todo','in_progress','done','overdue'] as $s)<option value="{{ $s }}" @selected($t->status === $s)>{{ ucfirst(str_replace('_',' ',$s)) }}</option>@endforeach
                        </select></form>
                    </td>
                    <td class="px-3 py-3 text-right">
                        <form method="POST" action="{{ route('admin.office.tasks.destroy', $t) }}" class="inline" onsubmit="return confirm('Hapus?')">@csrf @method('DELETE')<button class="text-xs text-red-700 hover:underline">Hapus</button></form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="p-10 text-center text-gray-500 italic font-serif">Belum ada tugas.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $tasks->links() }}</div>
    </div>
</div>
@endsection
