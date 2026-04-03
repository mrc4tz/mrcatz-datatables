{{-- File upload with inline image preview --}}
@php $sc = mrcatz_fb_classes('file-input', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    @if($field['preview'])
        <div class="mb-2 flex items-start gap-3">
            @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i', $field['preview']))
                <div class="shrink-0 overflow-hidden rounded-lg border border-base-content/10 cursor-zoom-in transition-opacity hover:opacity-80 {{ $field['previewClass'] ?? '' }}"
                     style="width: {{ $field['previewWidth'] ?? 80 }}px; height: {{ $field['previewHeight'] ?? 80 }}px;"
                     x-data @click="$dispatch('mrcatz-lightbox', { url: '{{ $field['preview'] }}' })">
                    <img src="{{ $field['preview'] }}" alt=""
                         style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;" />
                </div>
                <div class="text-xs text-base-content/40 mt-1">
                    <p>{{ mrcatz_lang('form_current_file') }}</p>
                    <p class="text-base-content/60">{{ mrcatz_lang('form_click_to_zoom') }}</p>
                </div>
            @else
                <a href="{{ $field['preview'] }}" target="_blank" class="inline-flex items-center gap-1.5 text-sm link link-primary">
                    {!! mrcatz_icon('download', 'text-sm') !!}
                    {{ basename($field['preview']) }}
                </a>
            @endif
        </div>
    @endif
    <input type="file"
           class="file-input file-input-bordered {{ $sc }} w-full @error($id) file-input-error @enderror @if($disabled) opacity-60 bg-base-200 @endif"
           {!! $wireDirective !!}
           @if($field['accept']) accept="{{ $field['accept'] }}" @endif
           @if($disabled) disabled @endif />
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
