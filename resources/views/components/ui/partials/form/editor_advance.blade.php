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

    // Build JS snippets in PHP so x-init has NO @if directives inside
    // it. Livewire's Blade compiler wraps @if/@endif with
    // <!--[if BLOCK]>...<![endif]--> HTML comments which would land
    // inside the x-init attribute as invalid JS.
    $uploadUrl     = json_encode($editorUploadUrl);
    $uploadPathJs  = $editorUploadPath ? json_encode($editorUploadPath) : null;
    $pathAppendJs  = $uploadPathJs ? "formData.append('path', {$uploadPathJs});" : '';
    $failMsg       = json_encode(mrcatz_lang('editor_upload_failed'));
    $modeJs        = json_encode($editorImageMode);

    $tinyUploadHandler = '';
    if ($editorImageMode === 'upload') {
        $tinyUploadHandler = "
                images_upload_url: {$uploadUrl},
                images_upload_handler: async (blobInfo) => {
                    const formData = new FormData();
                    formData.append('image', blobInfo.blob(), blobInfo.filename());
                    {$pathAppendJs}
                    const res = await fetch({$uploadUrl}, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                            'Accept': 'application/json',
                        },
                        body: formData,
                    });
                    if (!res.ok) {
                        const err = await res.json().catch(() => null);
                        const msg = err?.errors ? Object.values(err.errors).flat()[0] : (err?.message || {$failMsg});
                        throw new Error(msg);
                    }
                    const data = await res.json();
                    return data.url;
                },";
    }

    // Toolbar-image button override: open native file picker
    // immediately, then either upload or embed as base64 depending on
    // config — same pipeline as images_upload_handler, no dialog prompt.
    $imageButtonOnAction = "
                            const input = document.createElement('input');
                            input.type = 'file';
                            input.accept = 'image/*';
                            input.onchange = async () => {
                                const file = input.files?.[0];
                                if (!file) return;
                                if ({$modeJs} === 'upload') {
                                    try {
                                        const formData = new FormData();
                                        formData.append('image', file);
                                        {$pathAppendJs}
                                        const res = await fetch({$uploadUrl}, {
                                            method: 'POST',
                                            headers: {
                                                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                                                'Accept': 'application/json',
                                            },
                                            body: formData,
                                        });
                                        if (!res.ok) {
                                            const err = await res.json().catch(() => null);
                                            const msg = err?.errors ? Object.values(err.errors).flat()[0] : (err?.message || {$failMsg});
                                            editor.notificationManager.open({ text: msg, type: 'error' });
                                            return;
                                        }
                                        const data = await res.json();
                                        editor.insertContent('<img src=' + JSON.stringify(data.url) + ' alt=' + JSON.stringify(file.name || '') + ' />');
                                    } catch (e) {
                                        editor.notificationManager.open({ text: {$failMsg}, type: 'error' });
                                    }
                                } else {
                                    const reader = new FileReader();
                                    reader.onload = () => {
                                        editor.insertContent('<img src=' + JSON.stringify(reader.result) + ' alt=' + JSON.stringify(file.name || '') + ' />');
                                    };
                                    reader.readAsDataURL(file);
                                }
                            };
                            input.click();";
@endphp

<style>
    /* Match the Quill-backed editor() palette one-for-one so the two
       variants read as the same component family:
         Light: border #d1d5db
         Dark : border #4b5563, toolbar bg oklch(--b2/--b1),
                content bg oklch(--b1), text oklch(--bc),
                placeholder oklch(--bc/0.4),
                icons #9ca3af, hover #e5e7eb, active #60a5fa,
                pickers bg #1f2937 / border #4b5563. */
    .mrcatz-editor-adv { position: relative; }
    .mrcatz-editor-adv .tox-tinymce {
        border-radius: 0.5rem;
        border-width: 1px;
        border-color: #d1d5db;
    }
    /* Toolbar / edit area divider — match Quill's single 1px line
       between .ql-toolbar and .ql-container. Drop TinyMCE's own
       drop-shadow so we only see the clean border line. */
    .mrcatz-editor-adv .tox-editor-header {
        border-bottom: 1px solid #d1d5db !important;
        box-shadow: none !important;
    }

    /* Transparent chrome so the editor picks up the surrounding card /
       dialog / full-page form palette instead of stamping its own. */
    .mrcatz-editor-adv .tox-tinymce,
    .mrcatz-editor-adv .tox-editor-container,
    .mrcatz-editor-adv .tox-editor-header,
    .mrcatz-editor-adv .tox-menubar,
    .mrcatz-editor-adv .tox-toolbar,
    .mrcatz-editor-adv .tox-toolbar__primary,
    .mrcatz-editor-adv .tox-toolbar-overlord,
    .mrcatz-editor-adv .tox-edit-area,
    .mrcatz-editor-adv .tox-edit-area__iframe,
    .mrcatz-editor-adv .tox-statusbar {
        background-color: transparent !important;
    }

    /* Dark theme — mirror Quill's dark palette. */
    [data-theme*="dark"] .mrcatz-editor-adv .tox-tinymce {
        border-color: #4b5563;
    }
    [data-theme*="dark"] .mrcatz-editor-adv .tox-editor-header {
        border-bottom-color: #4b5563 !important;
    }
    [data-theme*="dark"] .mrcatz-editor-adv .tox-editor-header,
    [data-theme*="dark"] .mrcatz-editor-adv .tox-menubar,
    [data-theme*="dark"] .mrcatz-editor-adv .tox-toolbar__primary {
        background-color: oklch(var(--b2, var(--b1))) !important;
    }
    /* Toolbar / menubar buttons — icon + label color */
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn,
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-mbtn {
        color: #9ca3af !important;
    }
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn svg,
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-mbtn svg,
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-icon svg {
        fill: #9ca3af !important;
    }
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn:hover,
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-mbtn:hover {
        color: #e5e7eb !important;
        background-color: #374151 !important;
    }
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn:hover svg,
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-mbtn:hover svg {
        fill: #e5e7eb !important;
    }
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn--enabled,
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn--enabled:hover {
        color: #60a5fa !important;
        background-color: transparent !important;
    }
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn--enabled svg,
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn--enabled:hover svg {
        fill: #60a5fa !important;
    }
    /* Select arrow inside toolbar dropdowns (blocks, fontfamily, fontsize) */
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn__select-label {
        color: #9ca3af !important;
    }
    [data-theme*="dark"] .mrcatz-editor-adv .tox .tox-tbtn__select-chevron svg {
        fill: #9ca3af !important;
    }
    /* Dropdown panels (picker menus) */
    [data-theme*="dark"] .tox .tox-menu,
    [data-theme*="dark"] .tox .tox-collection--list {
        background-color: #1f2937 !important;
        border-color: #4b5563 !important;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3) !important;
    }
    [data-theme*="dark"] .tox .tox-collection__item {
        color: #9ca3af !important;
    }
    [data-theme*="dark"] .tox .tox-collection__item--active,
    [data-theme*="dark"] .tox .tox-collection__item:hover {
        color: #e5e7eb !important;
        background-color: #374151 !important;
    }
    [data-theme*="dark"] .tox .tox-collection__item svg {
        fill: #9ca3af !important;
    }
    /* Divider strokes between toolbar groups */
    [data-theme*="dark"] .mrcatz-editor-adv .tox-toolbar__group {
        border-color: #4b5563 !important;
    }
    /* Placeholder text in the iframe body — matched via content_style
       (see init below) using oklch(var(--bc) / 0.4). */

    /* Raise TinyMCE popup containers above DaisyUI's .modal-backdrop
       (z-index 50). We use dialog.show() instead of showModal() so
       everything competes via z-index — a high value on the aux keeps
       menus + color pickers visible over the dialog backdrop. */
    .tox-tinymce-aux,
    .tox-silver-sink {
        z-index: 2147483646 !important;
    }
</style>

<fieldset class="fieldset" id="fieldset-{{ $editorId }}"
    x-data
    x-init="
        (() => {
        // Host app loads TinyMCE in its layout (see docs). Poll briefly
        // here in case the script is still parsing when Alpine runs.
        // IIFE wrapper is required — Alpine/Livewire evaluate x-init as
        // `return <expression>` so top-level `const`/`let` statements
        // parse as syntax errors without the function wrap.
        const fieldset = document.getElementById('fieldset-{{ $editorId }}');
        // Resolve the nearest <dialog> ancestor so TinyMCE's popovers
        // (color picker, Insert-link dialog, media dialog, etc.) render
        // INSIDE the dialog's top layer instead of behind its backdrop.
        // Falls back to document.body for inline / full-page usage.
        const isDark = () => document.documentElement
            ?.getAttribute('data-theme')?.includes('dark');
        const uiContainer = fieldset?.closest('dialog') || document.body;

        const initTiny = () => {
            const selector = '#{{ $editorId }}';
            const el = document.getElementById('{{ $editorId }}');
            if (!el) {
                console.warn('[mrcatz] editorAdvance: target element not found', selector);
                return;
            }
            // Clean up any stale instance (e.g. after Livewire morph
            // re-inserted the fieldset) before attaching a new one.
            window.tinymce?.remove(selector);

            // Resolve the current DaisyUI palette from the parent
            // document and bake it into the iframe content_style.
            // Iframes don't inherit transparency from embed ancestors
            // the way regular divs do — transparent iframe body just
            // reveals the iframe element default white background.
            // Copying the host body computed background + color gives
            // a true follow-the-theme appearance that also flips on
            // theme toggle (we reinit on data-theme change, so these
            // values recompute each time).
            const hostStyle = getComputedStyle(document.body);
            const iframeBg    = hostStyle.backgroundColor || 'transparent';
            const iframeColor = hostStyle.color || 'inherit';
            // Placeholder color mirrors the Quill variant's
            // oklch(var(--bc) / 0.4) — resolve via a throwaway span
            // so the iframe gets a concrete rgba instead of a var
            // reference it can't look up.
            const probe = document.createElement('span');
            probe.style.color = 'oklch(var(--bc, 0.4) / 0.4)';
            document.body.appendChild(probe);
            const placeholderColor = getComputedStyle(probe).color;
            probe.remove();

            window.tinymce.init({
                target: el, // pass element directly — avoids selector scope quirks after morph
                license_key: 'gpl',
                // Pin asset URLs explicitly. TinyMCE's auto-detected
                // base_url falls back to the host page origin when the
                // main script was loaded from a CDN, which 404s every
                // skin/icon/plugin file and produces a toolbar-less
                // editor. skin_url + content_css_url bypass base_url
                // entirely and are the most reliable pins.
                // Swap skin + content CSS based on data-theme on <html>.
                // Reinit on theme toggle is handled by the MutationObserver
                // at the bottom of this script.
                skin_url: isDark()
                    ? 'https://cdn.jsdelivr.net/npm/tinymce@7/skins/ui/oxide-dark'
                    : 'https://cdn.jsdelivr.net/npm/tinymce@7/skins/ui/oxide',
                // Deliberately NOT setting content_css — the dark / default
                // content stylesheets both paint a solid body background
                // that fights our transparent override, leaving white
                // stripes behind paragraphs on dark themes. All content
                // typography is handled inline via content_style below.
                content_css: false,
                base_url: 'https://cdn.jsdelivr.net/npm/tinymce@7',
                suffix: '.min',
                // Render TinyMCE popovers / dialogs inside the nearest
                // <dialog> when this editor lives in a modal so they
                // don't get clipped behind the dialog's top-layer
                // backdrop. Body fallback for inline / full-page forms.
                ui_container: uiContainer,
                height: 500,
                menubar: 'file edit view insert format tools table help',
                plugins: 'advlist autolink lists link image charmap preview anchor pagebreak searchreplace wordcount visualblocks visualchars code fullscreen insertdatetime media nonbreaking table emoticons help',
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | subscript superscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link mrcatzimage media table | hr charmap emoticons | removeformat searchreplace | code fullscreen preview',
                toolbar_mode: 'sliding',
                placeholder: @js($placeholder),
                readonly: {{ $disabled ? 'true' : 'false' }},
                branding: false,
                promotion: false,
                resize: true,
                // Transparent iframe body so the editor picks up the
                // color scheme of whatever container it's embedded in
                // (card, dialog, full-page form). Text color is
                // inherited too — DaisyUI themes set a readable default
                // on the surrounding app.
                content_style:
                    `html, body { background: ${iframeBg} !important; color: ${iframeColor} !important; font-family: inherit; font-size: 14px; }` +
                    `p, h1, h2, h3, h4, h5, h6, ul, ol, li, blockquote, pre, table, td, th { background: transparent !important; color: inherit; }` +
                    `body[data-mce-placeholder]:not(.mce-visualblocks)::before { color: ${placeholderColor} !important; font-style: italic; }`,
                // Paste handling: strip Word-specific styling cruft so
                // content doesn't arrive carrying MSO-conditional HTML.
                paste_as_text: false,
                paste_data_images: false,
                {{-- {{ }} auto-escapes " to &quot; so the inner JSON
                     strings don't close x-init's outer double-quoted
                     HTML attribute. Browser decodes the entities back
                     before Alpine reads the attribute, so JS sees the
                     original quotes. {!! !!} would leak the literal " --}}
                {{ $tinyUploadHandler }}
                setup: (editor) => {
                    // Custom toolbar button that bypasses TinyMCE Insert
                    // Image dialog and opens a native file picker. We
                    // register under a unique name (mrcatzimage) so the
                    // image plugin cannot win a name race and replace
                    // our handler with its own dialog behaviour. The
                    // image plugin is still enabled for drag-drop /
                    // paste image handling via images_upload_handler.
                    editor.ui.registry.addButton('mrcatzimage', {
                        icon: 'image',
                        tooltip: editor.translate('Insert image'),
                        onAction: () => {
                            {{ $imageButtonOnAction }}
                        },
                    });

                    // Seed from Livewire on init + live-sync edits back.
                    editor.on('init', () => {
                        const initial = $wire.get('{{ $id }}');
                        if (initial) editor.setContent(initial);

                        // Popup relocation into <dialog> is no longer
                        // needed — the datatable-form now opens modal-
                        // data via dialog.show() (non-modal, no browser
                        // top layer), so TinyMCE popups at document.body
                        // can render above the dialog via regular
                        // z-index stacking.
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

        // Poll for the TinyMCE global — host app loads it in the layout,
        // but SPA navigation can run Alpine before the <script> finishes
        // parsing, so give it up to ~5 seconds.
        const waitForTiny = (attempts = 0) => {
            if (typeof window.tinymce === 'undefined') {
                if (attempts > 100) {
                    console.warn('[mrcatz] editorAdvance: TinyMCE not found on window. Add the CDN script to your layout: https://cdn.jsdelivr.net/npm/tinymce@7/tinymce.min.js');
                    return;
                }
                return setTimeout(() => waitForTiny(attempts + 1), 50);
            }
            initTiny();
        };

        // Watch <html data-theme> for light/dark toggles. TinyMCE can't
        // hot-swap its skin so we tear down and re-init with the new
        // theme's skin + content CSS — content is preserved via the
        // Livewire property so users don't lose edits mid-toggle.
        const themeObserver = new MutationObserver(() => {
            if (!window.tinymce) return;
            window.tinymce.remove('#{{ $editorId }}');
            initTiny();
        });
        themeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['data-theme', 'class'],
        });

        $nextTick(() => waitForTiny());
        })()
    "
>
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <div class="mrcatz-editor-adv w-full" wire:ignore>
        <textarea id="{{ $editorId }}" class="w-full"></textarea>
    </div>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
