{{-- Button field --}}
@php
    $btnStyle = $field['buttonStyle'] ?? 'primary';
    $btnSizeClass = !empty($field['size']) ? 'btn-' . $field['size'] : '';
    $btnClass = 'btn btn-' . $btnStyle . ' ' . $btnSizeClass;
    $btnTarget = $field['target'] ?? $field['onClick'];
@endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">&nbsp;</legend>
    <button type="button"
            class="{{ trim($btnClass) }} gap-2 w-full"
            wire:click="{{ $field['onClick'] }}"
            wire:loading.attr="disabled" wire:target="{{ $btnTarget }}"
            @if($disabled) disabled @endif>
        <span class="loading loading-spinner loading-xs"
              wire:loading wire:target="{{ $btnTarget }}"></span>
        @if($field['icon'])
            {!! mrcatz_form_icon($field['icon'], 'text-lg') !!}
        @endif
        {{ $field['label'] }}
    </button>
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
