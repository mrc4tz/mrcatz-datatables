<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach($fields as $field)
        @if(($field['type'] ?? 'text') === 'text')
            {{-- Text field --}}
            <div class="px-3 py-2 rounded-lg bg-base-200/40">
                <span class="text-[11px] text-base-content/40 block mb-0.5">{{ $field['label'] }}</span>
                <span class="text-sm text-base-content/80">{{ $field['value'] }}</span>
            </div>

        @elseif($field['type'] === 'image')
            {{-- Image field — click dispatches global lightbox event --}}
            <div class="px-3 py-2 rounded-lg bg-base-200/40">
                <span class="text-[11px] text-base-content/40 block mb-1">{{ $field['label'] }}</span>
                <div class="flex justify-start" @click.stop>
                    <div class="shrink-0 overflow-hidden {{ $field['previewClass'] }} {{ $field['url'] ? 'cursor-zoom-in transition-opacity hover:opacity-80' : '' }}"
                         style="width: {{ $field['width'] }}px; height: {{ $field['height'] }}px;"
                         @if($field['url']) @click="$dispatch('mrcatz-lightbox', { url: '{{ $field['url'] }}' })" @endif>
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
            {{-- Action link/button --}}
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
