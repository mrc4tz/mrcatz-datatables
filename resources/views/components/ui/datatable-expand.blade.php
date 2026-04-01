<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
    @foreach($fields as $field)
        <div class="px-3 py-2 rounded-lg bg-base-200/40">
            <span class="text-[11px] text-base-content/40 block mb-0.5">{{ $field['label'] }}</span>
            <span class="text-sm text-base-content/80">{{ $field['value'] }}</span>
        </div>
    @endforeach
</div>
