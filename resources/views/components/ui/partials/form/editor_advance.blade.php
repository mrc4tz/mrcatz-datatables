{{-- Advanced rich text editor using TinyMCE 7 (community edition).
     Matches WordPress Classic Editor feature set: tables, source view,
     fullscreen, hr, charmap, anchor, search/replace, full alignment,
     media embeds, etc.

     The basic editor() (Quill) stays for compact use cases; this
     variant is for long-form content like articles / news where
     authors expect the WP toolbar. --}}
@php
    $placeholder = $field['placeholder'] ?? mrcatz_lang('form_editor_placeholder');
    $editorId = 'mrcatz-editor-adv-' . $id;
    $editorImageMode = config('mrcatz.editor_image.mode', 'base64');
    $editorUploadUrl = $editorImageMode === 'upload' ? route('mrcatz.editor.upload-image') : '';
    $editorUploadPath = $field['uploadPath'] ?? null;
@endphp

<style>
    /* Container match the rest of the form field look — same border
       radius and border color language as the Quill editor. */
    .mrcatz-editor-adv { position: relative; }
    .mrcatz-editor-adv .tox-tinymce { border-radius: 0.5rem; border-color: #d1d5db; }
    /* Dark theme tweaks — use daisyUI CSS vars so TinyMCE picks up
       the host app's palette. TinyMCE has its own `oxide-dark` skin
       but loading both skins doubles the request; overriding the
       light-skin colors keeps bundle size tight. */
    [data-theme*="dark"] .mrcatz-editor-adv .tox-tinymce { border-color: #4b5563; }
    [data-theme*="dark"] .mrcatz-editor-adv .tox-toolbar__primary,
    [data-theme*="dark"] .mrcatz-editor-adv .tox-editor-header,
    [data-theme*="dark"] .mrcatz-editor-adv .tox-menubar { background-color: oklch(var(--b2, var(--b1))) !important; }
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn,
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-mbtn { color: oklch(var(--bc)) !important; }
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn svg { fill: oklch(var(--bc)) !important; }
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-edit-area__iframe { background-color: oklch(var(--b1)) !important; }
</style>

<fieldset class="fieldset" id="fieldset-{{ $editorId }}"
    x-data
    x-init="
        // Load TinyMCE lazily on first use — can't rely on @@assets
        // here because this is a partial (not a Livewire component
        // view). A plain <script> tag inside morphed HTML wouldn't
        // execute either. Inject the script element via JS so it
        // always runs regardless of Livewire's render path.
        const ensureTinyLoaded = () => new Promise((resolve) => {
            if (typeof window.tinymce !== 'undefined') return resolve();
            const existing = document.querySelector('script[data-mrcatz-tinymce]');
            if (existing) {
                existing.addEventListener('load', () => resolve(), { once: true });
                return;
            }
            const s = document.createElement('script');
            s.src = 'https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js';
            s.referrerPolicy = 'origin';
            s.setAttribute('data-mrcatz-tinymce', '');
            s.onload = () => resolve();
            document.head.appendChild(s);
        });

        const initTiny = async () => {
            await ensureTinyLoaded();
            const selector = '#{{ $editorId }}';
            const el = document.querySelector(selector);
            if (!el || el.dataset.tinyReady) return;
            el.dataset.tinyReady = '1';

            const isDark = document.documentElement
                ?.getAttribute('data-theme')?.includes('dark');

            window.tinymce.init({
                selector,
                license_key: 'gpl',
                height: 500,
                menubar: 'file edit view insert format tools table help',
                plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table emoticons help',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | subscript superscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | hr charmap emoticons | removeformat searchreplace | code fullscreen preview',
                placeholder: @js($placeholder),
                readonly: {{ $disabled ? 'true' : 'false' }},
                branding: false,
                promotion: false,
                resize: true,
                skin: isDark ? 'oxide-dark' : 'oxide',
                content_css: isDark ? 'dark' : 'default',
                content_style: 'body { font-family: inherit; font-size: 14px; }',
                // Paste handling: strip Word-specific styling cruft so
                // content doesn't arrive carrying MSO-conditional HTML.
                paste_as_text: false,
                paste_data_images: false,
                @if($editorImageMode === 'upload')
                images_upload_url: @js($editorUploadUrl),
                images_upload_handler: async (blobInfo) => {
                    const formData = new FormData();
                    formData.append('image', blobInfo.blob(), blobInfo.filename());
                    @if($editorUploadPath)
                    formData.append('path', @js($editorUploadPath));
                    @endif
                    const res = await fetch(@js($editorUploadUrl), {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    if (!res.ok) {
                        const err = await res.json().catch(() => null);
                        const msg = err?.errors ? Object.values(err.errors).flat()[0] : (err?.message || @js(mrcatz_lang('editor_upload_failed')));
                        throw new Error(msg);
                    }
                    const data = await res.json();
                    return data.url;
                },
                @endif
                setup: (editor) => {
                    // Seed from Livewire on init + live-sync edits back.
                    editor.on('init', () => {
                        const initial = $wire.get('{{ $id }}');
                        if (initial) editor.setContent(initial);
                    });
                    const sync = () => {
                        const html = editor.getContent();
                        $wire.set('{{ $id }}', html);
                    };
                    editor.on('change keyup undo redo', sync);

                    // Respond to external property updates (e.g. form
                    // reset on save success) — guard so we don't loop.
                    $wire.$watch('{{ $id }}', (value) => {
                        if ((value || '') !== editor.getContent()) {
                            editor.setContent(value || '');
                        }
                    });

                    // Red border on validation error, mirrored from the
                    // error message element that Blade renders below.
                    editor.on('init', () => {
                        const fieldset = document.getElementById('fieldset-{{ $editorId }}');
                        const wrapper = fieldset?.querySelector('.tox-tinymce');
                        const update = () => {
                            const hasError = fieldset?.querySelector('.text-error') !== null;
                            if (!wrapper) return;
                            wrapper.style.setProperty(
                                'border-color',
                                hasError ? (getComputedStyle(fieldset.querySelector('.text-error')).color) : '',
                                hasError ? 'important' : ''
                            );
                        };
                        const mo = new MutationObserver(update);
                        if (fieldset) mo.observe(fieldset, { childList: true, subtree: true });
                    });
                },
            });
        };
        $nextTick(initTiny);
    "
>
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <div class="mrcatz-editor-adv" wire:ignore>
        <textarea id="{{ $editorId }}"></textarea>
    </div>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
