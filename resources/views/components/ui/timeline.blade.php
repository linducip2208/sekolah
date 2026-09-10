@props([
    'items' => [],   // [['title','time'=>null,'desc'=>null,'tone'=>'primary|success|danger|...','done'=>bool]]
    'dense' => false,
])
<ol {{ $attributes->merge(['class' => 'relative space-y-' . ($dense ? '3' : '4')]) }} role="list">
    @foreach($items as $i => $item)
        @php
            $tone = $item['tone'] ?? ($i === 0 ? 'primary' : 'default');
            $color = $tone === 'default' ? 'var(--color-border-strong)' : "var(--color-{$tone})";
            $isLast = $i === count($items) - 1;
        @endphp
        <li class="relative flex gap-3{{ $isLast ? '' : ' pb-1' }}">
            {{-- Connector --}}
            @unless($isLast)
                <span class="absolute left-[7px] top-5 bottom-0 w-px" style="background: var(--color-border);" aria-hidden="true"></span>
            @endunless
            <span class="relative mt-1 h-[15px] w-[15px] rounded-full flex-shrink-0 flex items-center justify-center"
                  style="background: {{ $tone === 'default' ? 'var(--color-surface)' : "color-mix(in srgb, {$color} 16%, transparent)" }}; border: 2px solid {{ $color }};"
                  aria-hidden="true">
                @if(!empty($item['done']))
                    <span class="h-[5px] w-[5px] rounded-full" style="background: {{ $color }};"></span>
                @endif
            </span>
            <div class="min-w-0 flex-1 pb-0.5">
                <div class="flex items-baseline justify-between gap-2 flex-wrap">
                    <span class="text-sm font-semibold" style="color: var(--color-text);">{{ $item['title'] }}</span>
                    @if(!empty($item['time']))<span class="text-xs text-[var(--color-text-muted)] whitespace-nowrap">{{ $item['time'] }}</span>@endif
                </div>
                @if(!empty($item['desc']))<p class="text-xs text-[var(--color-text-secondary)] mt-0.5">{{ $item['desc'] }}</p>@endif
            </div>
        </li>
    @endforeach
</ol>
