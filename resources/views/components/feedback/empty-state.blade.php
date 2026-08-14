@props(['icon' => 'inbox', 'title' => null, 'description' => null, 'action' => null, 'actionHref' => null])
<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <div class="empty-icon" aria-hidden="true">
        <x-ui.icon :name="$icon" class="w-6 h-6" />
    </div>
    @if($title)<div class="empty-title">{{ $title }}</div>@endif
    @if($description)<p class="empty-desc">{{ $description }}</p>@endif
    @if($slot->isNotEmpty())<div class="mt-4">{{ $slot }}</div>@endif
    @if($actionHref)
        <a href="{{ $actionHref }}" class="btn btn-sm mt-4">{{ $action ?? 'Tambah' }}</a>
    @endif
</div>
