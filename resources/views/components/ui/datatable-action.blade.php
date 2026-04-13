{{-- DaisyUI tooltips default to z-index: 1 on their ::before/::after
     pseudos, which loses against the sticky table header (z-10). Bump
     the tooltip pseudos to z-20 so they float above the header when a
     hovered action button sits beneath it. Scoped to .mrcatz-action so
     it won't leak to other tooltips. --}}
<style>
    .mrcatz-action.tooltip::before,
    .mrcatz-action.tooltip::after {
        z-index: 20;
    }
</style>
<div class="flex justify-center gap-1">
    @if($editable)
        <button class="mrcatz-action btn btn-ghost btn-sm btn-square text-primary hover:bg-primary/10 transition-colors duration-200 tooltip tooltip-top" data-tip="{{ mrcatz_lang('tooltip_edit') }}"
                wire:click="editData({{ $data }})">
            {!! mrcatz_icon('edit', 'text-lg') !!}
        </button>
    @endif
    @if($deletable)
        <button class="mrcatz-action btn btn-ghost btn-sm btn-square text-error hover:bg-error/10 transition-colors duration-200 tooltip tooltip-top" data-tip="{{ mrcatz_lang('tooltip_delete') }}"
                wire:click="deleteData({{ $data }})">
            {!! mrcatz_icon('delete', 'text-lg') !!}
        </button>
    @endif
</div>
