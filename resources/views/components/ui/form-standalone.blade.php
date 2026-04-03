{{--
    MrCatz Standalone Form — use Form Builder outside of DataTable modal.

    Usage in any Livewire component that uses HasFormBuilder trait:

    @include('mrcatz::components.ui.form-standalone', [
        'submitMethod' => 'save',          // Livewire method to call on submit
        'submitLabel'  => 'Save Changes',  // Button label (default: 'Save')
        'submitIcon'   => 'check_circle',  // Button icon (default: 'check_circle')
        'submitStyle'  => 'primary',       // DaisyUI btn style (default: 'primary')
        'cancelUrl'    => route('dashboard'), // Optional cancel URL (shows cancel button if set)
        'cancelLabel'  => 'Cancel',        // Cancel button label (default: 'Cancel')
    ])
--}}

@php
    $submitMethod = $submitMethod ?? 'save';
    $submitLabel  = $submitLabel ?? mrcatz_lang('btn_save');
    $submitIcon   = $submitIcon ?? 'check_circle';
    $submitStyle  = $submitStyle ?? 'primary';
    $cancelUrl    = $cancelUrl ?? null;
    $cancelLabel  = $cancelLabel ?? mrcatz_lang('btn_cancel');
@endphp

<div>
    @include('mrcatz::components.ui.form-builder')

    <div class="flex items-center gap-3 mt-6 pt-4 border-t border-base-content/10">
        <button class="btn btn-{{ $submitStyle }} gap-2 px-6 shadow-sm"
                wire:click="{{ $submitMethod }}"
                wire:loading.attr="disabled"
                wire:target="{{ $submitMethod }}">
            <span class="loading loading-spinner loading-xs" wire:loading wire:target="{{ $submitMethod }}"></span>
            {!! mrcatz_icon($submitIcon, 'text-lg') !!}
            {{ $submitLabel }}
        </button>
        @if($cancelUrl)
            <a href="{{ $cancelUrl }}" class="btn btn-ghost">{{ $cancelLabel }}</a>
        @endif
    </div>
</div>
