{{-- Rich text editor using Quill.js --}}
{{-- Requires Quill CSS & JS loaded in layout --}}
@php
    $placeholder = $field['placeholder'] ?? mrcatz_lang('form_editor_placeholder');
    $editorId = 'mrcatz-editor-' . $id;
    $editorImageMode = config('mrcatz.editor_image.mode', 'base64');
    $editorUploadUrl = $editorImageMode === 'upload' ? route('mrcatz.editor.upload-image') : '';
    $editorUploadPath = $field['uploadPath'] ?? null;
@endphp

<style>
    .mrcatz-editor { position: relative; display: flex; flex-direction: column; }
    .mrcatz-editor .ql-toolbar.ql-snow { border-radius: 0.5rem 0.5rem 0 0; border-color: #d1d5db; flex-shrink: 0; }
    .mrcatz-editor .ql-container.ql-snow { border-radius: 0 0 0.5rem 0.5rem; border-color: #d1d5db; height: 250px; min-height: 150px; max-height: 80vh; font-size: 0.925rem; resize: vertical; overflow: hidden; }
    .mrcatz-editor .ql-editor { height: 100%; overflow-y: auto; }

    .mrcatz-editor .ql-editor { height: 100%; overflow-y: auto; }

    /* Dark theme only */
    [data-theme*="dark"] .mrcatz-editor .ql-toolbar.ql-snow { border-color: #4b5563; background-color: oklch(var(--b2, var(--b1))); }
    [data-theme*="dark"] .mrcatz-editor .ql-container.ql-snow { border-color: #4b5563; background-color: oklch(var(--b1)); }
    [data-theme*="dark"] .mrcatz-editor .ql-editor { color: oklch(var(--bc)); }
    [data-theme*="dark"] .mrcatz-editor .ql-editor.ql-blank::before { color: oklch(var(--bc) / 0.4); }
    [data-theme*="dark"] .mrcatz-editor .ql-snow .ql-stroke { stroke: #9ca3af; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow .ql-fill { fill: #9ca3af; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow .ql-picker { color: #9ca3af; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow .ql-picker-label { color: #9ca3af; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow .ql-picker-options { background-color: #1f2937 !important; border-color: #4b5563 !important; box-shadow: 0 4px 12px rgba(0,0,0,0.3); }
    [data-theme*="dark"] .mrcatz-editor .ql-snow .ql-picker-item { color: #9ca3af; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow .ql-picker-item:hover { color: #e5e7eb; background-color: #374151; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow button:hover .ql-stroke { stroke: #e5e7eb; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow button:hover .ql-fill { fill: #e5e7eb; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow .ql-picker-label:hover { color: #e5e7eb; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow button.ql-active .ql-stroke { stroke: #60a5fa; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow button.ql-active .ql-fill { fill: #60a5fa; }
    [data-theme*="dark"] .mrcatz-editor .ql-snow .ql-picker-label.ql-active { color: #60a5fa; }
</style>

<fieldset class="fieldset" id="fieldset-{{ $editorId }}"
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

            @if($editorImageMode === 'upload')
            quill.getModule('toolbar').addHandler('image', function() {
                const input = document.createElement('input');
                input.setAttribute('type', 'file');
                input.setAttribute('accept', 'image/*');
                input.click();

                input.onchange = async () => {
                    const file = input.files[0];
                    if (!file) return;

                    const formData = new FormData();
                    formData.append('image', file);
                    @if($editorUploadPath)
                    formData.append('path', @js($editorUploadPath));
                    @endif

                    try {
                        const res = await fetch(@js($editorUploadUrl), {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                                'Accept': 'application/json',
                            },
                            body: formData
                        });

                        if (!res.ok) {
                            const err = await res.json().catch(() => null);
                            const msg = err?.errors ? Object.values(err.errors).flat()[0] : (err?.message || @js(mrcatz_lang('editor_upload_failed')));
                            alert(msg);
                            return;
                        }

                        const data = await res.json();
                        const range = quill.getSelection(true);
                        quill.insertEmbed(range.index, 'image', data.url);
                        quill.setSelection(range.index + 1);
                    } catch (e) {
                        alert(@js(mrcatz_lang('editor_upload_failed')));
                    }
                };
            });
            @endif

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

            // Toggle red border on error
            const fieldset = document.getElementById('fieldset-{{ $editorId }}');
            const toolbar = fieldset.querySelector('.ql-toolbar');
            const container = fieldset.querySelector('.ql-container');

            function setErrorBorder(show) {
                let color = '';
                if (show) {
                    const errEl = fieldset.querySelector('.text-error');
                    color = errEl ? getComputedStyle(errEl).color : '#ef4444';
                }
                if (toolbar) toolbar.style.setProperty('border-color', color || '', show ? 'important' : '');
                if (container) container.style.setProperty('border-color', color || '', show ? 'important' : '');
            }

            const observer = new MutationObserver(() => {
                setErrorBorder(fieldset.querySelector('.text-error') !== null);
            });
            observer.observe(fieldset, { childList: true, subtree: true });
        })
    "
>
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <div class="mrcatz-editor" wire:ignore>
        <div id="{{ $editorId }}"></div>
    </div>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
