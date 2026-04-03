{{-- Image field: avatar/profile style with lightbox, upload, delete --}}
@php
    $sc = mrcatz_fb_classes('file-input', $field);
    $modalId = 'modal_delete_' . $id;
    $isUploadMode = !empty($field['onUpload']);
    $pvClass = $field['previewClass'] ?? 'rounded-full ring ring-primary ring-offset-base-100 ring-offset-2';
    $pw = $field['previewWidth'] ?? 128;
    $ph = $field['previewHeight'] ?? 128;
@endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <div class="flex flex-col items-center gap-4 @if($isUploadMode) p-4 border border-base-content/10 rounded-lg bg-base-200/20 @endif">
        {{-- Preview --}}
        <div class="shrink-0 overflow-hidden {{ $field['preview'] ? 'cursor-zoom-in transition-opacity hover:opacity-80' : '' }} {{ $pvClass }}"
             style="width: {{ $pw }}px; height: {{ $ph }}px;"
             @if($field['preview']) x-data @click="$dispatch('mrcatz-lightbox', { url: '{{ $field['preview'] }}' })" @endif>
            @if($field['preview'])
                <img src="{{ $field['preview'] }}" alt="{{ $field['label'] }}"
                     style="width:100%;height:100%;object-fit:cover;object-position:center;display:block;" />
            @else
                <div class="w-full h-full flex items-center justify-center {{ $field['fallback'] ? 'bg-primary/10' : 'bg-base-300' }}">
                    @if($field['fallback'])
                        <span class="text-4xl font-bold text-primary">{{ strtoupper(substr($field['fallback'], 0, 1)) }}</span>
                    @else
                        {!! mrcatz_form_icon('person', 'text-base-content/30 w-12 h-12') !!}
                    @endif
                </div>
            @endif
        </div>

        {{-- Upload UI --}}
        @if($isUploadMode)
            <div class="w-full max-w-xs">
                <input type="file"
                       class="file-input file-input-bordered file-input-sm {{ $sc }} w-full @error($id) file-input-error @enderror"
                       {!! $wireDirective !!}
                       @if($field['accept']) accept="{{ $field['accept'] }}" @endif
                       @if($disabled) disabled @endif />
                @include('mrcatz::components.ui.partials.form._error')

                <div class="flex gap-2 mt-3">
                    <button type="button"
                            class="btn btn-primary btn-sm flex-1 gap-1"
                            wire:click="{{ $field['onUpload'] }}"
                            wire:loading.attr="disabled"
                            wire:target="{{ $field['onUpload'] }},{{ $id }}">
                        <span class="loading loading-spinner loading-xs"
                              wire:loading wire:target="{{ $field['onUpload'] }},{{ $id }}"></span>
                        {{ mrcatz_lang('form_upload') }}
                    </button>
                    @if($field['onDelete'] && $field['preview'])
                        <button type="button"
                                class="btn btn-error btn-sm btn-outline gap-1"
                                onclick="document.getElementById('{{ $modalId }}').showModal()">
                            {!! mrcatz_icon('delete', 'text-sm') !!}
                        </button>
                    @endif
                </div>
            </div>
        @endif

        @include('mrcatz::components.ui.partials.form._hint')
    </div>
</fieldset>

{{-- Delete confirmation modal --}}
@if($field['onDelete'] && $field['preview'])
    <dialog id="{{ $modalId }}" class="modal modal-bottom sm:modal-middle">
        <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl">
            <div class="flex justify-center mb-4">
                <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center">
                    {!! mrcatz_icon('warning', 'text-3xl text-error') !!}
                </div>
            </div>
            <h3 class="text-lg font-bold text-center text-base-content">
                {{ $field['deleteConfirm'] ?? mrcatz_lang('form_delete_photo') }}
            </h3>
            <p class="py-4 text-center text-base-content/60 text-sm">
                {{ mrcatz_lang('form_delete_photo_desc') }}
            </p>
            <div class="modal-action justify-center gap-3">
                <button type="button"
                        class="btn btn-error gap-2 px-6 shadow-sm"
                        wire:click="{{ $field['onDelete'] }}"
                        onclick="document.getElementById('{{ $modalId }}').close()">
                    {!! mrcatz_icon('delete_forever', 'text-lg') !!}
                    {{ mrcatz_lang('form_btn_delete') }}
                </button>
                <form method="dialog">
                    <button class="btn btn-ghost">{{ mrcatz_lang('btn_cancel') }}</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
@endif
