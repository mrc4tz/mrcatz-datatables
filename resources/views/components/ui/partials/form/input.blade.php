{{-- Text-like inputs: text, email, password, url, tel, search, date, time, datetime-local --}}
@php
    $sc = mrcatz_fb_classes('input', $field);
    $pwToggle = $field['showPasswordToggle'] ?? true;
    $pwGenerate = $field['showPasswordGenerate'] ?? true;
    $hasPwButtons = $type === 'password' && !$disabled && ($pwToggle || $pwGenerate);
    // Native date/time/datetime pickers only open when the user clicks
    // the tiny indicator icon at the right of the input. Clicking the
    // wrapping label area normally just focuses — call showPicker()
    // programmatically so the whole field opens the native picker.
    $isPickerInput = in_array($type, ['date', 'time', 'datetime-local', 'month', 'week']);
@endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <label class="input input-bordered {{ $sc }} flex items-center gap-3 w-full transition-all duration-200
        focus-within:shadow-sm
        @if($isPickerInput) cursor-pointer @endif
        @error($errorKey ?? $id) input-error @enderror
        @if($disabled) opacity-60 bg-base-200 @endif"
        @if($isPickerInput && !$disabled)
        {{-- Only call showPicker when the click lands OUTSIDE the input
             itself — native input clicks already open the picker, a
             second programmatic call throws "NotAllowedError" and can
             close the picker back up. The label still looks clickable
             thanks to cursor-pointer, and any whitespace / icon area
             click now reliably opens the calendar. --}}
        @click="if ($event.target !== $el.querySelector('input')) { try { $el.querySelector('input').showPicker?.() } catch (e) {} }"
        @endif
        @if($type === 'password' && $hasPwButtons)
        x-data="{
            show: false,
            generate() {
                const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789!@#$%^&*';
                let pass = '';
                for (let i = 0; i < 16; i++) pass += chars.charAt(Math.floor(Math.random() * chars.length));
                $wire.set('{{ $id }}', pass);
                @if(!empty($field['confirmation']))
                try { $wire.set('{{ $id }}_confirmation', pass); } catch (e) {}
                @endif
                @if($pwToggle)
                this.show = true;
                @endif
            }
        }"
        @endif>
        @if($field['icon'])
            <span class="inline-flex items-center justify-center text-base-content/40 text-lg shrink-0 self-center">{!! mrcatz_form_icon($field['icon'], 'text-base-content/40 text-lg') !!}</span>
        @endif
        @if($field['prefix'])
            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['prefix'] }}</span>
        @endif
        <input @if($type === 'password' && $pwToggle && $hasPwButtons) :type="show ? 'text' : 'password'" @else type="{{ $type }}" @endif
               class="grow text-sm min-w-0"
               placeholder="{{ $field['placeholder'] ?? '...' }}"
               {!! $wireDirective !!}
               {!! $onChangeAttr !!}
               @if(in_array($type, ['datetime-local', 'time']) && !empty($field['step'])) step="{{ $field['step'] }}" @endif
               @if(in_array($type, ['date', 'time', 'datetime-local', 'month']) && !empty($field['min'])) min="{{ $field['min'] }}" @endif
               @if(in_array($type, ['date', 'time', 'datetime-local', 'month']) && !empty($field['max'])) max="{{ $field['max'] }}" @endif
               @if(in_array($type, ['date', 'time', 'datetime-local', 'month']) && (!empty($field['min']) || !empty($field['max'])))
               x-on:change="
                   (() => {
                       const el = $event.target;
                       const v = el.value;
                       if (!v) return;
                       const mn = el.getAttribute('min');
                       const mx = el.getAttribute('max');
                       let next = v;
                       if (mn && next < mn) next = mn;
                       if (mx && next > mx) next = mx;
                       if (next !== v) {
                           el.value = next;
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
        @if($type === 'datetime-local' && !$disabled)
            {{-- Affordance chevron on the right — native datetime-local
                 doesn't render a built-in indicator in most browsers
                 (unlike date / time which show a calendar / clock
                 icon), so users miss that the field is a picker. --}}
            <span class="inline-flex items-center justify-center shrink-0 self-center pointer-events-none">
                {!! mrcatz_form_icon('expand_more', 'text-base-content/40 w-5 h-5') !!}
            </span>
        @endif
        @if($hasPwButtons && $pwGenerate)
            <button type="button"
                    @click="generate()"
                    tabindex="-1"
                    class="shrink-0 text-base-content/40 hover:text-base-content/70 transition-colors cursor-pointer"
                    aria-label="Generate password"
                    title="Generate password">
                {!! mrcatz_form_icon('autorenew', 'text-base-content/40 text-lg') !!}
            </button>
        @endif
        @if($hasPwButtons && $pwToggle)
            <button type="button"
                    @click="show = !show"
                    tabindex="-1"
                    class="shrink-0 text-base-content/40 hover:text-base-content/70 transition-colors cursor-pointer"
                    :aria-label="show ? 'Hide password' : 'Show password'">
                <span x-show="!show">{!! mrcatz_form_icon('visibility', 'text-base-content/40 text-lg') !!}</span>
                <span x-show="show" x-cloak>{!! mrcatz_form_icon('visibility_off', 'text-base-content/70 text-lg') !!}</span>
            </button>
        @endif
    </label>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
