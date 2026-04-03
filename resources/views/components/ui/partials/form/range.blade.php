{{-- Range slider --}}
@php $sc = mrcatz_fb_classes('range', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <input type="range"
           class="range range-primary {{ $sc }} w-full @if($disabled) opacity-60 @endif"
           {!! $wireDirective !!}
           {!! $onChangeAttr !!}
           @if($field['min'] !== null) min="{{ $field['min'] }}" @endif
           @if($field['max'] !== null) max="{{ $field['max'] }}" @endif
           @if($field['step']) step="{{ $field['step'] }}" @endif
           @if($disabled) disabled @endif />
    <div class="flex justify-between text-xs text-base-content/50 px-1">
        <span>{{ $field['min'] ?? 0 }}</span>
        <span>{{ $field['max'] ?? 100 }}</span>
    </div>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
