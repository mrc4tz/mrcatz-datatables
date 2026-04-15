{{-- Number input --}}
@php $sc = mrcatz_fb_classes('input', $field); @endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <label class="input input-bordered {{ $sc }} flex items-center gap-3 w-full transition-all duration-200
        focus-within:shadow-sm
        @error($errorKey ?? $id) input-error @enderror
        @if($disabled) opacity-60 bg-base-200 @endif">
        @if($field['icon'])
            <span class="inline-flex items-center justify-center text-base-content/40 text-lg shrink-0 self-center">{!! mrcatz_form_icon($field['icon'], 'text-base-content/40 text-lg') !!}</span>
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
               @if($field['min'] !== null || $field['max'] !== null)
               x-on:change="
                   (() => {
                       const el = $event.target;
                       if (el.value === '') return;
                       const mn = el.getAttribute('min');
                       const mx = el.getAttribute('max');
                       const n = Number(el.value);
                       if (Number.isNaN(n)) return;
                       let next = n;
                       if (mn !== null && n < Number(mn)) next = Number(mn);
                       if (mx !== null && n > Number(mx)) next = Number(mx);
                       if (next !== n) {
                           el.value = String(next);
                           el.dispatchEvent(new Event('input', { bubbles: true }));
                           el.dispatchEvent(new Event('change', { bubbles: true }));
                       }
                   })()
               "
               @endif
               @if($disabled) disabled @endif />
        @if($field['suffix'])
            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['suffix'] }}</span>
        @endif
    </label>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
