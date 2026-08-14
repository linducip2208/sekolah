@props(['type' => 'line'])
@if($type === 'card')
    <div class="card card-pad space-y-3">
        <div class="skeleton h-4 w-1/3"></div>
        <div class="skeleton h-3 w-2/3"></div>
        <div class="skeleton h-3 w-1/2"></div>
    </div>
@elseif($type === 'table')
    <div class="card overflow-hidden">
        <div class="p-4 border-b border-[var(--color-border)]"><div class="skeleton h-4 w-1/4"></div></div>
        @for($i = 0; $i < 5; $i++)
            <div class="px-4 py-3 flex gap-3 border-b border-[var(--color-border)] last:border-0">
                <div class="skeleton h-3 flex-1"></div>
                <div class="skeleton h-3 flex-1"></div>
                <div class="skeleton h-3 w-24"></div>
            </div>
        @endfor
    </div>
@else
    <div class="skeleton h-4 w-full" {{ $attributes }}></div>
@endif
