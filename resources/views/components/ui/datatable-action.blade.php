<div class="flex justify-center gap-1">
    @if($editable)
        <button class="btn btn-ghost btn-sm btn-square text-primary hover:bg-primary/10 transition-colors duration-200 tooltip tooltip-top" data-tip="{{ mrcatz_lang('tooltip_edit') }}"
                wire:click="editData({{ $data }})">
            <span class="material-icons text-lg">edit</span>
        </button>
    @endif
    @if($deletable)
        <button class="btn btn-ghost btn-sm btn-square text-error hover:bg-error/10 transition-colors duration-200 tooltip tooltip-top" data-tip="{{ mrcatz_lang('tooltip_delete') }}"
                wire:click="deleteData({{ $data }})">
            <span class="material-icons text-lg">delete</span>
        </button>
    @endif
</div>
