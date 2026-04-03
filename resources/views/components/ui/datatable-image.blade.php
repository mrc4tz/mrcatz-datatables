<div class="flex items-center justify-center" @click.stop>
    <div class="shrink-0 overflow-hidden {{ $previewClass }} {{ $url ? 'cursor-zoom-in transition-opacity hover:opacity-80' : '' }}"
         style="width: {{ $width }}px; height: {{ $height }}px;"
         @if($url) x-data @click="$dispatch('mrcatz-lightbox', { url: '{{ $url }}' })" @endif>
        @if($url)
            <img src="{{ $url }}" alt=""
                 style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;" />
        @elseif($fallback)
            <div class="w-full h-full flex items-center justify-center bg-primary/10">
                <span class="text-xs font-bold text-primary">{{ strtoupper(substr($fallback, 0, 1)) }}</span>
            </div>
        @else
            <div class="w-full h-full flex items-center justify-center bg-base-300">
                {!! mrcatz_icon('person', 'text-base-content/30 w-4 h-4') !!}
            </div>
        @endif
    </div>
</div>
