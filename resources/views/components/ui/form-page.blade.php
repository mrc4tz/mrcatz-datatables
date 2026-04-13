{{-- Full-page form view — replaces the datatable area (NOT the whole
     viewport) when $modalFullScreen is enabled and the user is adding
     or editing. The page's app nav/header/footer stay visible above
     and below. Back button lives in the header; Save / Cancel sit in
     a sticky footer so they stay reachable at the end of long forms
     without scrolling back to the top. --}}

{{-- Entrance animation: small fade + slide-up when the form page
     mounts. Scoped class so it doesn't leak to other mounted content. --}}
<style>
    @keyframes mrcatzFormPageEnter {
        from { opacity: 0; transform: translateY(8px); }
        to   { opacity: 1; transform: translateY(0); }
    }
    .mrcatz-form-page {
        animation: mrcatzFormPageEnter 220ms cubic-bezier(0.16, 1, 0.3, 1);
    }
    @media (prefers-reduced-motion: reduce) {
        .mrcatz-form-page { animation: none; }
    }
</style>

<div class="mrcatz-form-page w-full bg-base-100 border border-base-content/10 rounded-xl overflow-hidden flex flex-col">
    {{-- Header bar — title on the left, close (×) icon on the right to
         match the dialog variant's muscle memory. --}}
    <div class="flex items-center justify-between gap-3 px-4 md:px-6 py-4 border-b border-base-content/10 bg-base-200/40 shrink-0">
        <h2 class="text-lg md:text-xl font-bold text-base-content flex items-center gap-2 min-w-0">
            {!! mrcatz_icon($isEdit ? 'edit_note' : 'add_circle', 'text-primary') !!}
            <span class="truncate">{{ $form_title ?: mrcatz_lang('default_form_title') }}</span>
        </h2>
        <button type="button"
                wire:click="closeFormPage"
                class="btn btn-ghost btn-sm btn-circle hover:bg-base-200 transition-colors shrink-0"
                aria-label="{{ mrcatz_lang('btn_cancel') }}">
            {!! mrcatz_icon('close') !!}
        </button>
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

    {{-- Sticky footer — Save / Cancel pinned to the viewport bottom
         so they're reachable at the end of long forms without
         scrolling back up. `sticky bottom-0` keeps them inside the
         panel while allowing the body above to scroll normally. --}}
    <div class="sticky bottom-0 flex items-center justify-end gap-2 px-4 md:px-6 py-3 border-t border-base-content/10 bg-base-100/95 backdrop-blur-sm shrink-0">
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
