@php
    $lightboxId = 'dt_lb_' . $columnKey . '_' . $index;
@endphp

<div class="flex items-center justify-center">
    <div class="shrink-0 overflow-hidden {{ $previewClass }} {{ $url ? 'cursor-zoom-in transition-opacity hover:opacity-80' : '' }}"
         style="width: {{ $width }}px; height: {{ $height }}px;"
         @if($url) onclick="document.getElementById('{{ $lightboxId }}').showModal()" @endif>
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

@if($url)
    <style>
        #{{ $lightboxId }} { background: none; border: none; outline: none; padding: 0; max-width: 100vw; max-height: 100vh; width: 100vw; height: 100vh; }
        #{{ $lightboxId }}::backdrop { background: rgba(0,0,0,0.85); backdrop-filter: blur(4px); }
        #{{ $lightboxId }}[open] { animation: mrcatz-lb-in 200ms ease-out; }
    </style>
    <dialog id="{{ $lightboxId }}"
            x-data="{ scale: 1, justReset: false }"
            @close="scale = 1; justReset = false"
            @wheel.prevent="scale = Math.min(5, Math.max(0.25, scale + ($event.deltaY < 0 ? 0.15 : -0.15)))">
        <div class="flex items-center justify-center w-full h-full cursor-default"
             @click.self="if(scale !== 1) { scale = 1; justReset = true; } else { $el.closest('dialog').close() }">
            <img src="{{ $url }}" alt=""
                 class="max-h-[85vh] max-w-[90vw] rounded-lg shadow-2xl transition-transform duration-200 origin-center select-none cursor-default"
                 draggable="false"
                 :style="'transform: scale(' + scale + ')'"
                 @click.stop="if(justReset) { justReset = false; return; } if(scale !== 1) { scale = 1; justReset = true; } else { $el.closest('dialog').close() }" />
        </div>
    </dialog>
@endif
@if(!isset($__mrcatz_lb_keyframes))
    @php $__mrcatz_lb_keyframes = true; @endphp
    <style>@keyframes mrcatz-lb-in { from { opacity:0; transform:scale(0.95); } to { opacity:1; transform:scale(1); } }</style>
@endif
