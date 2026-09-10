@props([
    'tabs' => [],   // [['label','url'|'href','active'=>bool,'count'=>null]]
])
<div {{ $attributes->merge(['class' => 'border-b border-[var(--color-border)] mb-4 -mx-1']) }} role="tablist">
    <div class="flex gap-0.5 overflow-x-auto px-1" style="-webkit-overflow-scrolling: touch; scrollbar-width: none;">
        @foreach($tabs as $tab)
            @php $url = $tab['url'] ?? ($tab['href'] ?? '#'); @endphp
            <a href="{{ $url }}" role="tab" aria-selected="{{ !empty($tab['active']) ? 'true' : 'false' }}"
               class="relative inline-flex items-center gap-1.5 whitespace-nowrap px-3.5 py-2.5 text-sm font-medium transition-colors min-h-[44px] sm:min-h-[40px]"
               style="{{ !empty($tab['active'])
                   ? 'color: var(--color-primary);'
                   : 'color: var(--color-text-secondary);' }}">
                {{ $tab['label'] }}
                @if(isset($tab['count']))
                    <span class="badge {{ !empty($tab['active']) ? 'badge-primary' : '' }}" style="font-size: 10px;">{{ $tab['count'] }}</span>
                @endif
                @if(!empty($tab['active']))
                    <span class="absolute inset-x-2 bottom-0 h-[2.5px] rounded-t-full" style="background: var(--color-primary);" aria-hidden="true"></span>
                @endif
            </a>
        @endforeach
    </div>
</div>
