<dialog id="modal-data" class="modal modal-bottom sm:modal-middle" wire:ignore.self aria-modal="true" aria-labelledby="modal-data-title">
    <div class="modal-box w-full max-w-3xl lg:max-w-4xl bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl" x-data x-trap.noscroll="document.getElementById('modal-data')?.open">
        {{-- Header --}}
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-base-content/10">
            <h3 id="modal-data-title" class="text-lg font-bold text-base-content flex items-center gap-2">
                {!! mrcatz_icon($isEdit ? 'edit_note' : 'add_circle', 'text-primary') !!}
                {{ $form_title ?: mrcatz_lang('default_form_title') }}
            </h3>
            <form method="dialog">
                <button class="btn btn-ghost btn-sm btn-circle hover:bg-base-200 transition-colors" aria-label="{{ mrcatz_lang('btn_cancel') }}">
                    {!! mrcatz_icon('close') !!}
                </button>
            </form>
        </div>

        {{-- Body --}}
        <div class="max-h-[60vh] overflow-y-auto pr-1 -mr-1">
            @if($this->hasFormBuilder())
                @include('mrcatz::components.ui.form-builder')
            @else
                <form>
                    @yield('forms')
                </form>
            @endif
        </div>

        {{-- Footer --}}
        <div class="modal-action pt-4 mt-4 border-t border-base-content/10">
            <button class="btn btn-primary gap-2 px-6 shadow-sm" wire:click="saveData">
                {!! mrcatz_icon('check_circle', 'text-lg') !!}
                {{ mrcatz_lang('btn_save') }}
            </button>
            <form method="dialog">
                <button class="btn btn-ghost">{{ mrcatz_lang('btn_cancel') }}</button>
            </form>
        </div>
    </div>

    {{-- Backdrop click-to-close: only rendered when the page component opts in.
         Default is false (modal stays open) so users don't lose in-progress
         edits when they accidentally click outside the modal box. --}}
    @if($this->modalDismissOnClickOutside ?? false)
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    @else
        {{-- Empty backdrop layer so the dim overlay still renders, but clicks
             on it do nothing. We also intercept the dialog's "cancel" event
             (fired by ESC) so accidental ESC presses don't dump the form. --}}
        <div class="modal-backdrop"></div>
        <script>
            (function () {
                const dlg = document.getElementById('modal-data');
                if (!dlg || dlg.dataset.mrcatzGuarded === '1') return;
                dlg.dataset.mrcatzGuarded = '1';
                dlg.addEventListener('cancel', (e) => e.preventDefault());
            })();
        </script>
    @endif
</dialog>

{{-- Full-page form mode. Rendered here (page-level blade) so $this
     resolves to the PAGE component that owns $modalFullScreen and
     $formPageVisible — those properties are NOT on the datatable
     child component's $this. When visible, the overlay sits on top
     of the datatable via position:fixed inset-0. --}}
@if(($this->modalFullScreen ?? false) && ($this->formPageVisible ?? false))
    <div class="fixed inset-0 z-40 bg-base-100 overflow-y-auto">
        <div class="container mx-auto px-4 lg:px-6">
            @include('mrcatz::components.ui.form-page')
        </div>
    </div>
@endif

<dialog id="modal-data-delete" class="modal modal-bottom sm:modal-middle" wire:ignore.self aria-modal="true" aria-labelledby="modal-delete-title">
    <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl" x-data x-trap.noscroll="document.getElementById('modal-data-delete')?.open">
        {{-- Icon --}}
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center">
                {!! mrcatz_icon('warning', 'text-3xl text-error') !!}
            </div>
        </div>

        <h3 id="modal-delete-title" class="text-lg font-bold text-center text-base-content">{{ mrcatz_lang('confirm_delete') }}</h3>
        <p class="py-4 text-center text-base-content/60 text-sm">
            {{ mrcatz_lang('confirm_delete_text') }}<br>
            <span class="font-semibold text-base-content mt-1 inline-block">{{ $deleted_text }}</span>
        </p>

        <div class="modal-action justify-center gap-3">
            <button class="btn btn-error gap-2 px-6 shadow-sm" wire:click="dropData">
                {!! mrcatz_icon('delete_forever', 'text-lg') !!}
                {{ mrcatz_lang('btn_yes_delete') }}
            </button>
            <form method="dialog">
                <button class="btn btn-ghost">{{ mrcatz_lang('btn_cancel') }}</button>
            </form>
        </div>
    </div>

    @if($this->deleteModalDismissOnClickOutside ?? true)
        <form method="dialog" class="modal-backdrop">
            <button>close</button>
        </form>
    @else
        <div class="modal-backdrop"></div>
        <script>
            (function () {
                const dlg = document.getElementById('modal-data-delete');
                if (!dlg || dlg.dataset.mrcatzGuarded === '1') return;
                dlg.dataset.mrcatzGuarded = '1';
                dlg.addEventListener('cancel', (e) => e.preventDefault());
            })();
        </script>
    @endif
</dialog>
