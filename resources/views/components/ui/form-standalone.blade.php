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
        'buttonAlign'  => 'right',         // Button alignment: 'left', 'center', 'right', 'full' (default: 'right')
        'buttonCard'   => false,           // Wrap buttons inside a card (default: false)
    ])
--}}

@php
    $submitMethod = $submitMethod ?? 'save';
    $submitLabel  = $submitLabel ?? mrcatz_lang('btn_save');
    $submitIcon   = $submitIcon ?? 'check_circle';
    $submitStyle  = $submitStyle ?? 'primary';
    $cancelUrl    = $cancelUrl ?? null;
    $cancelLabel  = $cancelLabel ?? mrcatz_lang('btn_cancel');
    $buttonAlign  = $buttonAlign ?? 'right';
    $buttonCard   = $buttonCard ?? false;

    $alignClass = match($buttonAlign) {
        'left'   => 'sm:justify-start',
        'center' => 'sm:justify-center',
        'full'   => '',
        default  => 'sm:justify-end',
    };

    $btnClass = $buttonAlign === 'full' ? 'btn-block' : '';
@endphp

<div>
    @include('mrcatz::components.ui.form-builder')

    @if($buttonCard)
    <div class="card bg-base-100 shadow-sm mt-6">
        <div class="card-body">
    @endif

    <div class="flex flex-col-reverse sm:flex-row {{ $alignClass }} items-stretch sm:items-center gap-3 {{ $buttonCard ? '' : 'mt-6 pt-4 border-t border-base-content/10' }}">
        @if($cancelUrl)
            <a href="{{ $cancelUrl }}" class="btn btn-ghost {{ $btnClass }}">{{ $cancelLabel }}</a>
        @endif
        <button class="btn btn-{{ $submitStyle }} gap-2 px-6 shadow-sm {{ $btnClass }}"
                wire:click="{{ $submitMethod }}"
                wire:loading.attr="disabled"
                wire:target="{{ $submitMethod }}">
            <span class="loading loading-spinner loading-xs" wire:loading wire:target="{{ $submitMethod }}"></span>
            {!! mrcatz_icon($submitIcon, 'text-lg') !!}
            {{ $submitLabel }}
        </button>
    </div>

    @if($buttonCard)
        </div>
    </div>
    @endif
</div>
