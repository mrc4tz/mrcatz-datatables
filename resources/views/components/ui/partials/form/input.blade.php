{{-- Text-like inputs: text, email, password, url, tel, search, date, time, datetime-local --}}
@php $sc = mrcatz_fb_classes('input', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <label class="input input-bordered {{ $sc }} flex items-center gap-3 w-full transition-all duration-200
        focus-within:shadow-sm
        @error($id) input-error @enderror
        @if($disabled) opacity-60 bg-base-200 @endif"
        @if($type === 'password') x-data="{ show: false }" @endif>
        @if($field['icon'])
            <span class="text-base-content/40 text-lg shrink-0">{!! mrcatz_form_icon($field['icon'], 'text-base-content/40 text-lg') !!}</span>
        @endif
        @if($field['prefix'])
            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['prefix'] }}</span>
        @endif
        <input @if($type === 'password') :type="show ? 'text' : 'password'" @else type="{{ $type }}" @endif
               class="grow text-sm min-w-0"
               placeholder="{{ $field['placeholder'] ?? '...' }}"
               {!! $wireDirective !!}
               {!! $onChangeAttr !!}
               @if($disabled) disabled @endif />
        @if($field['suffix'])
            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['suffix'] }}</span>
        @endif
        @if($type === 'password' && !$disabled)
            <button type="button"
                    @click="show = !show"
                    tabindex="-1"
                    class="shrink-0 text-base-content/40 hover:text-base-content/70 transition-colors cursor-pointer"
                    :aria-label="show ? 'Hide password' : 'Show password'">
                <span x-show="!show">{!! mrcatz_form_icon('visibility', 'text-base-content/40 text-lg') !!}</span>
                <span x-show="show" x-cloak>{!! mrcatz_form_icon('visibility_off', 'text-base-content/70 text-lg') !!}</span>
            </button>
        @endif
    </label>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
