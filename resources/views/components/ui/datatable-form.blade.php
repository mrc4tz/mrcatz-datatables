<dialog id="modal-data" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
    <div class="modal-box w-full max-w-3xl lg:max-w-4xl bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl">
        {{-- Header --}}
        <div class="flex items-center justify-between pb-4 mb-4 border-b border-base-content/10">
            <h3 class="text-lg font-bold text-base-content flex items-center gap-2">
                <span class="material-icons text-primary">{{ $isEdit ? 'edit_note' : 'add_circle' }}</span>
                {{ $form_title ?: mrcatz_lang('default_form_title') }}
            </h3>
            <form method="dialog">
                <button class="btn btn-ghost btn-sm btn-circle hover:bg-base-200 transition-colors">
                    <span class="material-icons">close</span>
                </button>
            </form>
        </div>

        {{-- Body --}}
        <form>
            <div class="flex-col max-h-[60vh] overflow-y-auto pr-1 -mr-1">
                @yield('forms')
            </div>
        </form>

        {{-- Footer --}}
        <div class="modal-action pt-4 mt-4 border-t border-base-content/10">
            <button class="btn btn-primary gap-2 px-6 shadow-sm" wire:click="saveData">
                <span class="material-icons text-lg">check_circle</span>
                {{ mrcatz_lang('btn_save') }}
            </button>
            <form method="dialog">
                <button class="btn btn-ghost">{{ mrcatz_lang('btn_cancel') }}</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>

<dialog id="modal-data-delete" class="modal modal-bottom sm:modal-middle" wire:ignore.self>
    <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl">
        {{-- Icon --}}
        <div class="flex justify-center mb-4">
            <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center">
                <span class="material-icons text-error text-3xl">warning</span>
            </div>
        </div>

        <h3 class="text-lg font-bold text-center text-base-content">{{ mrcatz_lang('confirm_delete') }}</h3>
        <p class="py-4 text-center text-base-content/60 text-sm">
            {{ mrcatz_lang('confirm_delete_text') }}<br>
            <span class="font-semibold text-base-content mt-1 inline-block">{{ $deleted_text }}</span>
        </p>

        <div class="modal-action justify-center gap-3">
            <button class="btn btn-error gap-2 px-6 shadow-sm" wire:click="dropData">
                <span class="material-icons text-lg">delete_forever</span>
                {{ mrcatz_lang('btn_yes_delete') }}
            </button>
            <form method="dialog">
                <button class="btn btn-ghost">{{ mrcatz_lang('btn_cancel') }}</button>
            </form>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop">
        <button>close</button>
    </form>
</dialog>
