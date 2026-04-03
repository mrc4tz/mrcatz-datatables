@php $expandDialogs = []; @endphp
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach($fields as $field)
        @if(($field['type'] ?? 'text') === 'text')
            {{-- Text field --}}
            <div class="px-3 py-2 rounded-lg bg-base-200/40">
                <span class="text-[11px] text-base-content/40 block mb-0.5">{{ $field['label'] }}</span>
                <span class="text-sm text-base-content/80">{{ $field['value'] }}</span>
            </div>

        @elseif($field['type'] === 'image')
            {{-- Image field --}}
            @php
                $expandLbId = 'expand_lb_' . md5($field['label'] . ($field['url'] ?? '') . uniqid());
                if ($field['url']) $expandDialogs[] = ['id' => $expandLbId, 'url' => $field['url']];
            @endphp
            <div class="px-3 py-2 rounded-lg bg-base-200/40">
                <span class="text-[11px] text-base-content/40 block mb-1">{{ $field['label'] }}</span>
                <div class="flex justify-center" @click.stop>
                    <div class="shrink-0 overflow-hidden {{ $field['previewClass'] }} {{ $field['url'] ? 'cursor-zoom-in transition-opacity hover:opacity-80' : '' }}"
                         style="width: {{ $field['width'] }}px; height: {{ $field['height'] }}px;"
                         @if($field['url']) onclick="event.stopPropagation(); document.getElementById('{{ $expandLbId }}').showModal()" @endif>
                        @if($field['url'])
                            <img src="{{ $field['url'] }}" alt=""
                                 style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;" />
                        @elseif($field['fallback'] ?? null)
                            <div class="w-full h-full flex items-center justify-center bg-primary/10">
                                <span class="text-lg font-bold text-primary">{{ strtoupper(substr($field['fallback'], 0, 1)) }}</span>
                            </div>
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-base-300">
                                {!! mrcatz_icon('person', 'text-base-content/30 w-6 h-6') !!}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

        @elseif($field['type'] === 'button')
            {{-- Download/attachment link --}}
            <div class="px-3 py-2 rounded-lg bg-base-200/40" @click.stop>
                <span class="text-[11px] text-base-content/40 block mb-1">{{ $field['label'] }}</span>
                <a href="{{ $field['url'] }}"
                   class="inline-flex items-center gap-1.5 text-sm link link-primary hover:link-hover"
                   @if($field['download'] ?? false) download @endif
                   @if($field['target'] ?? null) target="{{ $field['target'] }}" @endif>
                    @if($field['icon'] ?? null)
                        {!! mrcatz_form_icon($field['icon'], 'text-sm') !!}
                    @endif
                    {{ $field['buttonLabel'] }}
                </a>
            </div>

        @elseif($field['type'] === 'link')
            {{-- Action link/button with hook --}}
            <div class="px-3 py-2 rounded-lg bg-base-200/40" @click.stop>
                <span class="text-[11px] text-base-content/40 block mb-1.5">{{ $field['label'] }}</span>
                <a href="{{ $field['url'] }}"
                   class="btn btn-{{ $field['style'] ?? 'ghost' }} btn-sm gap-1.5"
                   @if($field['target'] ?? null) target="{{ $field['target'] }}" @endif>
                    @if($field['icon'] ?? null)
                        {!! mrcatz_form_icon($field['icon'], 'text-sm') !!}
                    @endif
                    {{ $field['buttonLabel'] }}
                </a>
            </div>
        @endif
    @endforeach
</div>

{{-- Lightbox dialogs rendered OUTSIDE the grid to avoid overflow clipping --}}
@foreach($expandDialogs as $dlg)
    <style>
        #{{ $dlg['id'] }} { background:none;border:none;outline:none;padding:0;max-width:100vw;max-height:100vh;width:100vw;height:100vh; }
        #{{ $dlg['id'] }}::backdrop { background:rgba(0,0,0,0.85);backdrop-filter:blur(4px); }
        #{{ $dlg['id'] }}[open] { animation:mrcatz-lb-in 200ms ease-out; }
    </style>
    <dialog id="{{ $dlg['id'] }}"
            x-data="{ scale:1, justReset:false }"
            @close="scale=1;justReset=false"
            @wheel.prevent="scale=Math.min(5,Math.max(0.25,scale+($event.deltaY<0?0.15:-0.15)))">
        <div class="flex items-center justify-center w-full h-full cursor-default"
             @click.self="if(scale!==1){scale=1;justReset=true}else{$el.closest('dialog').close()}">
            <img src="{{ $dlg['url'] }}" alt=""
                 class="max-h-[85vh] max-w-[90vw] rounded-lg shadow-2xl transition-transform duration-200 origin-center select-none cursor-default"
                 draggable="false" :style="'transform:scale('+scale+')'"
                 @click.stop="if(justReset){justReset=false;return}if(scale!==1){scale=1;justReset=true}else{$el.closest('dialog').close()}" />
        </div>
    </dialog>
@endforeach
@if(!isset($__mrcatz_lb_keyframes))
    @php $__mrcatz_lb_keyframes = true; @endphp
    <style>@keyframes mrcatz-lb-in{from{opacity:0;transform:scale(0.95)}to{opacity:1;transform:scale(1)}}</style>
@endif
