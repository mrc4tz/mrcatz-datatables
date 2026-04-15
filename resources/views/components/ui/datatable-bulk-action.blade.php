{{-- MrCatz custom bulk action modal.
     Renders the modal for the currently active bulk action (either a
     confirmation dialog or a form). Shown/hidden via Livewire-dispatched
     browser events `open-bulk-action-modal` / `close-bulk-action-modal`.
--}}
@php
    $bulkActions = $this->getBulkActions();
@endphp

@if(!empty($bulkActions))
<div
    x-data="{
        open: false,
        mode: null,
        init() {
            window.addEventListener('open-bulk-action-modal', (e) => {
                this.mode = (e.detail && e.detail.mode) || null;
                this.open = true;
            });
            window.addEventListener('close-bulk-action-modal', () => {
                this.open = false;
                this.mode = null;
            });
        }
    }"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-[60] flex items-end sm:items-center justify-center"
    aria-modal="true"
    role="dialog"
>
    <div class="absolute inset-0 bg-black/40" x-on:click="$wire.cancelBulkAction()"></div>

    @php
        $active = $this->getBulkActionById($this->activeBulkActionId);
    @endphp

    @if($active)
        @if($active['mode'] === 'confirmation')
            <div class="relative modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-sm text-center">
                <div class="w-14 h-14 rounded-full bg-warning/10 flex items-center justify-center mx-auto mb-4">
                    {!! mrcatz_icon($active['icon'], 'text-2xl text-warning') !!}
                </div>
                <h3 class="text-base font-bold text-base-content mb-1">{{ $active['formTitle'] }}</h3>
                @if(!empty($active['formSubtitle']))
                    <p class="text-sm text-base-content/60 mb-5">{{ $active['formSubtitle'] }}</p>
                @else
                    <div class="mb-5"></div>
                @endif
                <p class="text-xs text-base-content/50 mb-5">
                    {{ count($selectedRows) }} {{ mrcatz_lang('data_selected') }}
                </p>
                <div class="flex gap-2 justify-center">
                    <button type="button" class="btn btn-ghost btn-sm" wire:click="cancelBulkAction">
                        {{ mrcatz_lang('btn_cancel') }}
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" wire:click="processBulkAction">
                        {{ $active['buttonText'] }}
                    </button>
                </div>
            </div>
        @else
            {{-- form mode --}}
            @php
                $bulkFields   = $this->getBulkFormFields();
                $bulkSection  = $this->getBulkFormSection();
            @endphp
            <div class="relative modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl w-full max-w-2xl">
                <div class="flex items-start justify-between border-b border-base-content/10 pb-3 mb-4">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center shrink-0">
                            {!! mrcatz_icon($active['icon'], 'text-xl text-primary') !!}
                        </div>
                        <div class="min-w-0">
                            <h3 class="text-base font-bold text-base-content truncate">{{ $active['formTitle'] }}</h3>
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
                        {{ count($selectedRows) }} {{ mrcatz_lang('data_selected') }}
                    </span>
                    <div class="flex gap-2">
                        <button type="button" class="btn btn-ghost btn-sm" wire:click="cancelBulkAction">
                            {{ mrcatz_lang('btn_cancel') }}
                        </button>
                        <button type="button" class="btn btn-primary btn-sm gap-1" wire:click="processBulkAction">
                            {!! mrcatz_icon($active['icon'], 'text-sm') !!}
                            <span>{{ $active['buttonText'] }}</span>
                        </button>
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
@endif
