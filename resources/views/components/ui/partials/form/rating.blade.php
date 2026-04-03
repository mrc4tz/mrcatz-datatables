{{-- Star rating --}}
@php $sc = mrcatz_fb_classes('rating', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <div class="rating {{ $sc }} @if($disabled) opacity-60 @endif">
        @for($ri = 1; $ri <= ($field['max'] ?? 5); $ri++)
            <input type="radio"
                   name="rating_{{ $id }}"
                   class="mask mask-star-2 bg-warning"
                   value="{{ $ri }}"
                   {!! $wireDirective !!}
                   @if($disabled) disabled @endif />
        @endfor
    </div>
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
