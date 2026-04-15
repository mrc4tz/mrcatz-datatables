{{-- Textarea --}}
@php $sc = mrcatz_fb_classes('textarea', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <textarea class="textarea w-full textarea-bordered {{ $sc }} h-28 text-sm transition-all duration-200
        focus:shadow-sm
        @error($errorKey ?? $id) textarea-error @enderror
        @if($disabled) opacity-60 bg-base-200 @endif"
              placeholder="{{ $field['placeholder'] ?? '...' }}"
              {!! $wireDirective !!}
              {!! $onChangeAttr !!}
              @if($disabled) disabled @endif></textarea>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
