{{-- Range slider with a live value badge pinned top-right of the
     field so users can read the current value at a glance while
     dragging — no hover / focus dance to show the number. --}}
@php
    $sc = mrcatz_fb_classes('range', $field);
    $rangeMin = $field['min'] ?? 0;
    $rangeMax = $field['max'] ?? 100;
@endphp
<fieldset class="fieldset"
    x-data="{ val: Number($wire.get('{{ $id }}') ?? {{ $rangeMin }}) }"
    x-init="$wire.$watch('{{ $id }}', v => val = Number(v ?? {{ $rangeMin }}))">
    {{-- Legend stays exactly as the other form fields render it.
         When `showValue` is true (default), badge is inlined into the
         legend as a float-right element so the legend's own baseline /
         padding / font-size stay untouched. Opt out with
         `MrCatzFormField::range(..., showValue: false)`. --}}
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">
        {{ $field['label'] }}
        @if($field['showValue'] ?? true)
            <span class="float-right inline-flex items-center px-2 py-0.5 rounded-md bg-primary/10 text-primary text-xs font-semibold tabular-nums normal-case"
                  x-text="val"></span>
        @endif
    </legend>
    <input type="range"
           class="range range-primary {{ $sc }} w-full @if($disabled) opacity-60 @endif"
           {!! $wireDirective !!}
           {!! $onChangeAttr !!}
           @input="val = Number($event.target.value)"
           @if($field['min'] !== null) min="{{ $field['min'] }}" @endif
           @if($field['max'] !== null) max="{{ $field['max'] }}" @endif
           @if($field['step']) step="{{ $field['step'] }}" @endif
           @if($disabled) disabled @endif />
    <div class="flex justify-between text-xs text-base-content/50 px-1">
        <span>{{ $rangeMin }}</span>
        <span>{{ $rangeMax }}</span>
    </div>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
