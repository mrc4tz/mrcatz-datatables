{{-- Single checkbox --}}
@php $sc = mrcatz_fb_classes('checkbox', $field); @endphp
<fieldset class="fieldset">
    <label class="label cursor-pointer justify-start gap-3 p-3 rounded-lg border border-base-content/10 hover:bg-base-200/50 transition-colors duration-200
        @if($disabled) opacity-60 bg-base-200 @endif">
        <input type="checkbox"
               class="checkbox checkbox-primary {{ $sc }}"
               {!! $wireDirective !!}
               {!! $onChangeAttr !!}
               @if($disabled) disabled @endif />
        <span class="text-base-content text-sm font-medium">{{ $field['label'] }}</span>
    </label>
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
