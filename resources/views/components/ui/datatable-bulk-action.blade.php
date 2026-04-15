{{-- MrCatz custom bulk action modal (page-level).
     Uses a native <dialog open> wrapper with DaisyUI `.modal modal-open`
     classes so `.modal-box` inherits the visible state — without it,
     DaisyUI applies `opacity: 0` to the box by default.

     Tailwind safelist — these dynamic color classes below must stay
     visible to the JIT so they aren't purged:
     btn-primary btn-secondary btn-accent btn-neutral btn-info btn-success btn-warning btn-error btn-ghost
     bg-primary/10 bg-secondary/10 bg-accent/10 bg-neutral/10 bg-info/10 bg-success/10 bg-warning/10 bg-error/10 bg-ghost/10
     text-primary text-secondary text-accent text-neutral text-info text-success text-warning text-error text-ghost --}}
@php
    $active = $this->activeBulkAction ?? [];
    $hasActive = !empty($this->activeBulkActionId) && !empty($active);
@endphp

@if($hasActive)
    @php
        $activeColor = $active['buttonColor'] ?? 'primary';
    @endphp
    <dialog
        open
        wire:key="mrcatz-bulk-action-{{ $active['id'] }}"
        class="modal modal-open modal-bottom sm:modal-middle"
        aria-modal="true"
        role="dialog"
    >
        @if(($active['mode'] ?? '') === 'confirmation')
            <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-sm text-center">
                <div class="w-14 h-14 rounded-full bg-{{ $activeColor }}/10 flex items-center justify-center mx-auto mb-4">
                    {!! mrcatz_icon($active['icon'] ?? 'edit', 'text-2xl text-' . $activeColor) !!}
                </div>
                <h3 class="text-base font-bold text-base-content mb-1">{{ $active['formTitle'] ?? $active['buttonText'] }}</h3>
                @if(!empty($active['formSubtitle']))
                    <p class="text-sm text-base-content/60 mb-5">{{ $active['formSubtitle'] }}</p>
                @else
                    <div class="mb-5"></div>
                @endif
                <p class="text-xs text-base-content/50 mb-5">
                    {{ count($this->bulkSelectedRows ?? []) }} {{ mrcatz_lang('data_selected') }}
                </p>
                <div class="flex gap-2 justify-center">
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="cancelBulkAction">
                        {{ mrcatz_lang('btn_cancel') }}
                    </button>
                    <button type="button" class="btn btn-{{ $activeColor }} btn-sm" wire:click="processBulkAction">
                        {{ $active['buttonText'] }}
                    </button>
                </div>
            </div>
        @else
            {{-- form mode --}}
            @php
                $bulkFields  = $this->getBulkFormFields();
                $bulkSection = $this->getBulkFormSection();
            @endphp
            <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl w-full max-w-2xl">
                <div class="flex items-start justify-between border-b border-base-content/10 pb-3 mb-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-{{ $activeColor }}/10 flex items-center justify-center shrink-0">
                            {!! mrcatz_icon($active['icon'] ?? 'edit', 'text-xl text-' . $activeColor) !!}
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-base-content truncate">{{ $active['formTitle'] ?? $active['buttonText'] }}</h3>
                            @if(!empty($active['formSubtitle']))
                                <p class="text-xs text-base-content/60 truncate">{{ $active['formSubtitle'] }}</p>
                            @endif
                        </div>
                    </div>
                    <button type="button" class="btn btn-ghost btn-sm btn-circle" wire:click="cancelBulkAction">
                        {!! mrcatz_icon('close') !!}
                    </button>
                </div>

                <div class="max-h-[60vh] overflow-y-auto pr-1">
                    @if(!empty($bulkFields))
                        @include('mrcatz::components.ui.form-builder', ['formFields' => $bulkFields])
                    @elseif($bulkSection)
                        @yield($bulkSection)
                    @endif
                </div>

                <div class="flex justify-between items-center mt-5 pt-3 border-t border-base-content/10">
                    <span class="text-xs text-base-content/50">
                        {{ count($this->bulkSelectedRows ?? []) }} {{ mrcatz_lang('data_selected') }}
                    </span>
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="cancelBulkAction">
                            {{ mrcatz_lang('btn_cancel') }}
                        </button>
                        <button type="button" class="btn btn-{{ $activeColor }} btn-sm gap-1" wire:click="processBulkAction">
                            {!! mrcatz_icon($active['icon'] ?? 'edit', 'text-sm') !!}
                            <span>{{ $active['buttonText'] }}</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Click-outside dismiss. Mirrors DaisyUI's <form method="dialog"> trick
             but wires to wire:click so Livewire resets state cleanly. --}}
        <div class="modal-backdrop" wire:click="cancelBulkAction"></div>
    </dialog>
@endif
