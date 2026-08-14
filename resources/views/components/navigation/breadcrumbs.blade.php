@props(['items' => []])
<nav aria-label="Breadcrumb" class="crumb">
    <ol class="flex items-center gap-1 min-w-0">
        @foreach($items as $i => $item)
            @php $label = is_array($item) ? ($item['label'] ?? '') : $item; $url = is_array($item) ? ($item['url'] ?? null) : null; @endphp
            <li class="flex items-center gap-1 min-w-0">
                @if(!$loop->first)<span class="sep" aria-hidden="true">/</span>@endif
                @if($url && !$loop->last)
                    <a href="{{ $url }}">{{ $label }}</a>
                @else
                    <span class="current">{{ $label }}</span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
