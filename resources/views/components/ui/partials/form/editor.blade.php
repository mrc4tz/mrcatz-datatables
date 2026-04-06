{{-- Rich text editor using Quill.js --}}
{{-- Requires Quill CSS & JS loaded in layout --}}
@php
    $placeholder = $field['placeholder'] ?? mrcatz_lang('form_editor_placeholder');
    $editorId = 'mrcatz-editor-' . $id;
@endphp

<style>
    .mrcatz-editor { position: relative; display: flex; flex-direction: column; }
    .mrcatz-editor .ql-toolbar { border-radius: 0.5rem 0.5rem 0 0; border-color: oklch(var(--bc) / 0.15); flex-shrink: 0; }
    .mrcatz-editor .ql-container { border-radius: 0 0 0.5rem 0.5rem; border-color: oklch(var(--bc) / 0.15); height: 250px; min-height: 150px; max-height: 80vh; font-size: 0.925rem; resize: vertical; overflow: hidden; }
    .mrcatz-editor .ql-editor { height: 100%; overflow-y: auto; }
    .mrcatz-editor.editor-error .ql-container,
    .mrcatz-editor.editor-error .ql-toolbar { border-color: oklch(var(--er)); }
</style>

<fieldset class="fieldset"
    x-data
    x-init="
        $nextTick(() => {
            const el = document.getElementById('{{ $editorId }}');
            if (!el || el.dataset.quillReady) return;
            el.dataset.quillReady = '1';

            const quill = new Quill(el, {
                theme: 'snow',
                placeholder: @js($placeholder),
                readOnly: {{ $disabled ? 'true' : 'false' }},
                modules: {
                    toolbar: [
                        [{ 'header': [2, 3, 4, false] }],
                        ['bold', 'italic', 'underline', 'strike'],
                        [{ 'color': [] }, { 'background': [] }],
                        [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                        ['blockquote', 'link', 'image'],
                        ['clean']
                    ]
                }
            });

            const initial = $wire.get('{{ $id }}');
            if (initial) quill.root.innerHTML = initial;

            quill.on('text-change', () => {
                const html = quill.root.innerHTML;
                $wire.set('{{ $id }}', html === '<p><br></p>' ? '' : html);
            });

            $wire.$watch('{{ $id }}', (value) => {
                if (value !== quill.root.innerHTML) {
                    quill.root.innerHTML = value || '';
                }
            });
        })
    "
>
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <div class="mrcatz-editor @error($id) editor-error @enderror" wire:ignore>
        <div id="{{ $editorId }}"></div>
    </div>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
