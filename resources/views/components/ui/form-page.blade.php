{{-- Full-page form view — replaces the datatable area (NOT the whole
     viewport) when $modalFullScreen is enabled and the user is adding
     or editing. The page's app nav/header/footer stay visible above
     and below. Back button + Save/Cancel trigger closeFormPage() and
     saveData() on the page component. --}}
<div class="w-full bg-base-100 border border-base-content/10 rounded-xl overflow-hidden">
    {{-- Header bar --}}
    <div class="flex items-center justify-between gap-4 px-4 md:px-6 py-4 border-b border-base-content/10 bg-base-200/40">
        <div class="flex items-center gap-3 min-w-0">
            <button type="button"
                    wire:click="closeFormPage"
                    class="btn btn-ghost btn-sm btn-circle hover:bg-base-200 transition-colors shrink-0"
                    aria-label="{{ mrcatz_lang('btn_cancel') }}">
                {!! mrcatz_icon('arrow_back') !!}
            </button>
            <h2 class="text-lg md:text-xl font-bold text-base-content flex items-center gap-2 min-w-0">
                {!! mrcatz_icon($isEdit ? 'edit_note' : 'add_circle', 'text-primary') !!}
                <span class="truncate">{{ $form_title ?: mrcatz_lang('default_form_title') }}</span>
            </h2>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button"
                    wire:click="closeFormPage"
                    class="btn btn-ghost btn-sm">
                {{ mrcatz_lang('btn_cancel') }}
            </button>
            <button type="button"
                    wire:click="saveData"
                    class="btn btn-primary btn-sm gap-2 px-5 shadow-sm">
                {!! mrcatz_icon('check_circle', 'text-lg') !!}
                {{ mrcatz_lang('btn_save') }}
            </button>
        </div>
    </div>

    {{-- Form body — inner gutter keeps long-form content breathable. --}}
    <div class="px-4 md:px-8 lg:px-12 py-6 md:py-8">
        <div class="max-w-5xl mx-auto">
            @if($this->hasFormBuilder())
                @include('mrcatz::components.ui.form-builder')
            @else
                <form>
                    @yield('forms')
                </form>
            @endif
        </div>
    </div>
</div>
