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

    // Build the image upload handler snippet as a raw JS string here
    // in PHP so x-init has NO @if directives inside it. Livewire's
    // Blade compiler wraps @if/@endif with <!--[if BLOCK]>...<![endif]-->
    // HTML comments (for morph safety), and those comments become
    // invalid JS when they land inside an x-init expression — you
    // get "Unexpected token '.'" the moment Alpine tries to parse it.
    $tinyUploadHandler = '';
    if ($editorImageMode === 'upload') {
        $uploadUrl  = json_encode($editorUploadUrl);
        $uploadPath = $editorUploadPath ? json_encode($editorUploadPath) : null;
        $pathAppend = $uploadPath ? "formData.append('path', {$uploadPath});" : '';
        $failMsg    = json_encode(mrcatz_lang('editor_upload_failed'));
        $tinyUploadHandler = "
                images_upload_url: {$uploadUrl},
                images_upload_handler: async (blobInfo) => {
                    const formData = new FormData();
                    formData.append('image', blobInfo.blob(), blobInfo.filename());
                    {$pathAppend}
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
@endphp

<style>
    /* Match surrounding form field border/radius language. */
    .mrcatz-editor-adv { position: relative; }
    .mrcatz-editor-adv .tox-tinymce { border-radius: 0.5rem; }

    /* Transparent backgrounds so the editor inherits whatever palette
       the surrounding container (card, dialog, full-page form) uses.
       Covers the iframe wrapper, toolbar, menubar, statusbar, and the
       editor's status footer. The iframe body itself is transparent
       via `content_style` in the init config below. */
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

    /* When the editor lives inside a <dialog>.showModal() (top-layer),
       we append .tox-tinymce-aux into that dialog so popovers ride
       the top layer too. That aux container can inherit unintended
       styles from the dialog — keep its layout clean. */
    dialog .tox-tinymce-aux {
        position: relative;
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
                content_css: isDark()
                    ? 'https://cdn.jsdelivr.net/npm/tinymce@7/skins/content/dark/content.min.css'
                    : 'https://cdn.jsdelivr.net/npm/tinymce@7/skins/content/default/content.min.css',
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
                toolbar: 'undo redo | blocks fontfamily fontsize | bold italic underline strikethrough | forecolor backcolor | subscript superscript | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media table | hr charmap emoticons | removeformat searchreplace | code fullscreen preview',
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
                content_style: 'html, body { background: transparent !important; font-family: inherit; font-size: 14px; color: inherit; }',
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
                    // Seed from Livewire on init + live-sync edits back.
                    editor.on('init', () => {
                        const initial = $wire.get('{{ $id }}');
                        if (initial) editor.setContent(initial);

                        // Move TinyMCE's shared aux container (where
                        // dropdowns, color pickers, and modal dialogs
                        // render) INTO the ancestor <dialog> if we're
                        // inside one. <dialog>.showModal() creates a
                        // top-layer stacking context and anything
                        // outside that layer paints behind its backdrop
                        // regardless of z-index — so TinyMCE's popups
                        // at document.body were hidden. The ui_container
                        // option we'd tried first is ignored in v7, so
                        // we relocate manually. Tear-down moves it back
                        // (see below).
                        const hostDialog = fieldset?.closest('dialog');
                        const aux = document.querySelector('.tox-tinymce-aux');
                        if (hostDialog && aux && aux.parentElement !== hostDialog) {
                            aux.dataset.mrcatzOriginalParent ||= 'body';
                            hostDialog.appendChild(aux);
                        }
                    });
                    editor.on('remove', () => {
                        const aux = document.querySelector('.tox-tinymce-aux');
                        if (aux && aux.dataset.mrcatzOriginalParent === 'body'
                                && aux.parentElement !== document.body) {
                            document.body.appendChild(aux);
                        }
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
