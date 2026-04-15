{{-- Tag input - users can type and add tags as string array --}}
@php
    $placeholder = $field['placeholder'] ?? 'Ketik lalu tekan Enter';
@endphp
<fieldset class="fieldset" x-data="{
    newTag: '',
    addTag() {
        let tag = this.newTag.trim();
        if (tag === '') return;
        let current = [...($wire.get('{{ $id }}') || [])];
        if (!current.includes(tag)) {
            current.push(tag);
            $wire.set('{{ $id }}', current);
        }
        this.newTag = '';
    },
    removeTag(index) {
        let current = [...($wire.get('{{ $id }}') || [])];
        current.splice(index, 1);
        $wire.set('{{ $id }}', current);
    }
}">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <div class="p-3 border border-base-content/15 rounded-lg bg-base-200/30 min-h-12 @error($errorKey ?? $id) border-error @enderror">
        <div class="flex flex-wrap gap-2 mb-2">
            @foreach(($this->{$id} ?? []) as $index => $tag)
                <span class="badge badge-primary gap-1 text-sm">
                    {{ $tag }}
                    @if(!$disabled)
                        <button type="button" x-on:click="removeTag({{ $index }})" class="btn btn-ghost btn-xs btn-circle !h-5 !w-5 !min-h-0 flex items-center justify-center">
                            {!! mrcatz_icon('close', 'w-3 h-3') !!}
                        </button>
                    @endif
                </span>
            @endforeach
        </div>
        @if(!$disabled)
            <input type="text"
                   class="input input-sm input-bordered w-full text-sm"
                   placeholder="{{ $placeholder }}"
                   x-model="newTag"
                   x-on:keydown.enter.prevent="addTag()"
                   x-on:keydown.comma.prevent="addTag()" />
        @endif
    </div>
    <p class="text-base-content/50 text-xs mt-1">{{ mrcatz_lang('form_taginput_hint') }}</p>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
