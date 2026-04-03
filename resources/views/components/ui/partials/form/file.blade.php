{{-- File upload (basic) --}}
@php $sc = mrcatz_fb_classes('file-input', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    @if($field['preview'])
        <div class="mb-2">
            @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg)(\?.*)?$/i', $field['preview']))
                <img src="{{ $field['preview'] }}" alt="Preview" class="max-h-32 rounded-lg border border-base-content/10 object-cover" />
            @else
                <a href="{{ $field['preview'] }}" target="_blank" class="link link-primary text-sm flex items-center gap-1">
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
