<div class="flex justify-center gap-1">
    @if($editable)
        <button class="btn btn-ghost btn-sm btn-square text-primary hover:bg-primary/10 transition-colors duration-200 tooltip tooltip-top" data-tip="{{ mrcatz_lang('tooltip_edit') }}"
                wire:click="editData({{ $data }})">
            {!! mrcatz_icon('edit', 'text-lg') !!}
        </button>
    @endif
    @if($deletable)
        <button class="btn btn-ghost btn-sm btn-square text-error hover:bg-error/10 transition-colors duration-200 tooltip tooltip-top" data-tip="{{ mrcatz_lang('tooltip_delete') }}"
                wire:click="deleteData({{ $data }})">
            {!! mrcatz_icon('delete', 'text-lg') !!}
        </button>
    @endif
</div>
