{{-- Number input --}}
@php $sc = mrcatz_fb_classes('input', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <label class="input input-bordered {{ $sc }} flex items-center gap-3 w-full transition-all duration-200
        focus-within:shadow-sm
        @error($id) input-error @enderror
        @if($disabled) opacity-60 bg-base-200 @endif">
        @if($field['icon'])
            <span class="text-base-content/40 text-lg shrink-0">{!! mrcatz_form_icon($field['icon'], 'text-base-content/40 text-lg') !!}</span>
        @endif
        @if($field['prefix'])
            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['prefix'] }}</span>
        @endif
        <input type="number"
               class="grow text-sm min-w-0"
               placeholder="{{ $field['placeholder'] ?? '...' }}"
               {!! $wireDirective !!}
               {!! $onChangeAttr !!}
               @if($field['step']) step="{{ $field['step'] }}" @endif
               @if($field['min'] !== null) min="{{ $field['min'] }}" @endif
               @if($field['max'] !== null) max="{{ $field['max'] }}" @endif
               @if($disabled) disabled @endif />
        @if($field['suffix'])
            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['suffix'] }}</span>
        @endif
    </label>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
