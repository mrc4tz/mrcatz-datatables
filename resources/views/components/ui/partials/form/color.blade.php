{{-- Color picker --}}
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <input type="color"
           class="w-16 h-10 rounded-lg border border-base-content/15 cursor-pointer @if($disabled) opacity-60 @endif"
           {!! $wireDirective !!}
           {!! $onChangeAttr !!}
           @if($disabled) disabled @endif />
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
