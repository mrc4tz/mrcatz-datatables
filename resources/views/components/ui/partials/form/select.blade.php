{{-- Select dropdown --}}
@php $sc = mrcatz_fb_classes('select', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <select class="select select-bordered {{ $sc }} w-full text-sm transition-all duration-200
        focus:shadow-sm
        @error($id) select-error @enderror
        @if($disabled) opacity-60 bg-base-200 @endif"
            {!! $wireDirective !!}
            {!! $onChangeAttr !!}
            @if($disabled) disabled @endif>
        <option value="">-- {{ $field['label'] }} --</option>
        @foreach(($field['data'] ?? []) as $d)
            @php
                $optVal = is_array($d) ? ($d[$field['valueKey']] ?? '') : (is_object($d) ? ($d->{$field['valueKey']} ?? '') : '');
                $optLabel = is_array($d) ? ($d[$field['optionKey']] ?? '') : (is_object($d) ? ($d->{$field['optionKey']} ?? '') : '');
            @endphp
            <option value="{{ $optVal }}">{{ $optLabel }}</option>
        @endforeach
    </select>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
