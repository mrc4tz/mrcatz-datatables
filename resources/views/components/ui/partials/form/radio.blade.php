{{-- Radio buttons --}}
@php $sc = mrcatz_fb_classes('radio', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <div class="flex flex-wrap gap-4 p-3 border border-base-content/10 rounded-lg @if($disabled) opacity-60 bg-base-200 @endif">
        @foreach(($field['options'] ?? []) as $val => $label)
            <label class="flex items-center gap-2 cursor-pointer">
                <input type="radio"
                       name="radio_{{ $id }}"
                       class="radio radio-primary {{ $sc }}"
                       value="{{ $val }}"
                       {!! $wireDirective !!}
                       {!! $onChangeAttr !!}
                       @if($disabled) disabled @endif />
                <span class="text-sm">{{ $label }}</span>
            </label>
        @endforeach
    </div>
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
