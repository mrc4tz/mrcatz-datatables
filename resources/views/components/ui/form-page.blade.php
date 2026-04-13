{{-- Full-page form view — replaces the entire datatable when
     $modalFullScreen is enabled and the user is adding or editing a
     row. Renders the same Form Builder used by the dialog, wrapped in
     a page-level header (back button + title) and footer (Save +
     Cancel). The back button triggers closeFormPage() which flips
     $formPageVisible back off and restores the datatable. --}}
<div class="w-full">
    {{-- Header bar — sticky so the back button / title stay reachable
         while the form body scrolls. --}}
    <div class="sticky top-0 z-20 bg-base-100 border-b border-base-content/10 mb-6">
        <div class="flex items-center justify-between gap-4 py-4">
            <div class="flex items-center gap-3 min-w-0">
                <button type="button"
                        wire:click="closeFormPage"
                        class="btn btn-ghost btn-sm btn-circle hover:bg-base-200 transition-colors shrink-0"
                        aria-label="{{ mrcatz_lang('btn_cancel') }}">
                    {!! mrcatz_icon('arrow_back') !!}
                </button>
                <h2 class="text-xl font-bold text-base-content flex items-center gap-2 min-w-0">
                    {!! mrcatz_icon($isEdit ? 'edit_note' : 'add_circle', 'text-primary') !!}
                    <span class="truncate">{{ $form_title ?: mrcatz_lang('default_form_title') }}</span>
                </h2>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button type="button"
                        wire:click="closeFormPage"
                        class="btn btn-ghost">
                    {{ mrcatz_lang('btn_cancel') }}
                </button>
                <button type="button"
                        wire:click="saveData"
                        class="btn btn-primary gap-2 px-6 shadow-sm">
                    {!! mrcatz_icon('check_circle', 'text-lg') !!}
                    {{ mrcatz_lang('btn_save') }}
                </button>
            </div>
        </div>
    </div>

    {{-- Form body — full page width with a comfortable max-width so
         long-form fields (rich editors, textareas) read well on large
         screens without spanning edge to edge. --}}
    <div class="max-w-5xl mx-auto pb-12">
        @if($this->hasFormBuilder())
            @include('mrcatz::components.ui.form-builder')
        @else
            <form>
                @yield('forms')
            </form>
        @endif
    </div>
</div>
