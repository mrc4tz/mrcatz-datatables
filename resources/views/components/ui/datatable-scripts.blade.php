@push('scripts')
    @script
    <script>
        $wire.on('reset-select', (val) => {
            let prefix = val[1];
            val[0].forEach(f => {
                let id = f.id + "_" + prefix;
                let select = document.getElementById(id);
                if (select) select.value = '';
            });
        });

        // Baked at render time from the page component so we don't rely on
        // $wire.propertyName resolving across nested component boundaries.
        // When true, the server flips $formPageVisible on prepare events and
        // the component re-renders as a full-page form — no dialog needed.
        const isFullScreen = @json((bool) ($this->modalFullScreen ?? false));

        // When returning from the full-page form to the datatable, the
        // viewport is usually parked near the bottom (Save/Cancel bar).
        // Prefer scrolling to the top of the nested datatable child
        // component (the livewire:...-table wrapper just above the
        // toolbar) rather than the outer page component — otherwise
        // users land above unrelated hero/intro content that sits
        // inside the page wrapper but outside the datatable.
        // Measure the total vertical space reserved by fixed/sticky
        // elements anchored at the top of the viewport (app nav, docs
        // sub-nav, announcement bars, etc). Walks the DOM once per call,
        // which is fine for the occasional "close form" scroll — not hot
        // path. Takes the furthest .bottom so stacked sticky bars all
        // stay visible after the scroll lands.
        const getStickyTopHeight = () => {
            let bottom = 0;
            for (const el of document.body.querySelectorAll('*')) {
                const cs = getComputedStyle(el);
                if (cs.position !== 'fixed' && cs.position !== 'sticky') continue;
                const rect = el.getBoundingClientRect();
                // Only count things actually painted at the top of the
                // viewport — `rect.top <= 8` catches small negative
                // offsets from shadows/transforms without sweeping in
                // bottom-anchored sticky footers.
                if (rect.top <= 8 && rect.bottom > 0 && rect.bottom > bottom) {
                    bottom = rect.bottom;
                }
            }
            return bottom;
        };

        // Immediate visual gap above the datatable — either the host
        // container's own top padding (e.g. `p-4` on the wrapper) or the
        // datatable's own top margin. We include just THIS gap in the
        // scroll, not whatever content may sit further up the page, so
        // the table lands right under the sticky bar with the exact
        // breathing room the host layout already specifies.
        const getImmediateTopGap = (el) => {
            const targetMarginTop = parseFloat(getComputedStyle(el).marginTop) || 0;
            const parent = el.parentElement;
            const parentPaddingTop = parent
                ? parseFloat(getComputedStyle(parent).paddingTop) || 0
                : 0;
            return targetMarginTop + parentPaddingTop;
        };

        const scrollPageToTop = () => {
            if (!isFullScreen) return;
            const target = document.querySelector('[data-mrcatz-datatable-root]')
                || $wire.$el
                || document.querySelector('[wire\\:id]');
            if (!target) {
                return window.scrollTo({ top: 0, behavior: 'smooth' });
            }
            // Manually scroll (instead of scrollIntoView) so we can
            // subtract sticky-header height + preserve the immediate
            // container gap. scrollIntoView only honours scroll-margin-
            // top, a single static CSS value that can't react to runtime
            // layout variations.
            const rect = target.getBoundingClientRect();
            const stickyOffset = getStickyTopHeight();
            const gap = getImmediateTopGap(target);
            const top = window.scrollY + rect.top - stickyOffset - gap;
            window.scrollTo({ top: Math.max(0, top), behavior: 'smooth' });
        };

        // Map a datatable pageName → modal DOM-id suffix. Default setPageName
        // ('page') = no suffix (backward compat for single-CRUD pages).
        // Non-default pageName = '-<pageName>' (multi-CRUD pages scope each
        // modal separately so two tables on the same page don't open the
        // same `<dialog id="modal-data">`).
        const mrcatzModalSuffix = (pageName) => (!pageName || pageName === 'page') ? '' : '-' + pageName;

        $wire.on('add-data', (d) => {
            // Payload shape: [pageName] (string). When an old table dispatches
            // without a pageName, d[0] is undefined → defaults to 'page'.
            const pageName = d[0] || 'page';
            // Forward pageName to the page component's listenAddData() wrapper
            // so it can set $currentCrudPageName before the user's
            // prepareAddData hook fires. Livewire matches the named arg
            // `pageName` to the `$pageName` param on listenAddData — if the
            // user's hook doesn't declare it, PHP silently discards the arg.
            $wire.dispatch('prepareAddData', { pageName });
            // .show() instead of .showModal() so the dialog does NOT
            // enter the browser top layer — top-layer dialogs paint
            // their backdrop above every outside element (any z-index)
            // which hides TinyMCE's popovers (font picker, color
            // picker, Insert Link). DaisyUI's .modal-backdrop div and
            // Alpine x-trap cover the interaction / focus-trap pieces.
            if (!isFullScreen) document.getElementById('modal-data' + mrcatzModalSuffix(pageName))?.show();
        });

        $wire.on('edit-data', (d) => {
            // Payload shape: [data, pageName]. Old single-arg dispatches keep
            // d[1] undefined → pageName defaults to 'page'.
            const data = d[0];
            const pageName = d[1] || 'page';
            $wire.dispatch('prepareEditData', { data, pageName });
            if (!isFullScreen) document.getElementById('modal-data' + mrcatzModalSuffix(pageName))?.show();
        });

        // Scroll on Cancel / save-success; the × button in the header
        // calls closeFormPage(false) which only dispatches
        // 'mrcatz-form-page-closed' (show the datatable again) WITHOUT
        // 'mrcatz-form-page-scroll', so scroll is skipped there.
        // Double rAF gives Livewire's morph a frame to insert the
        // datatable back into the DOM before we measure its position.
        $wire.on('mrcatz-form-page-scroll', () => {
            requestAnimationFrame(() => requestAnimationFrame(scrollPageToTop));
        });

        $wire.on('refresh-data', (d) => {
            let data = d[0];
            // dispatch_to_view() on the page component includes
            // `pageName = $this->currentCrudPageName` in the payload so we
            // close the correct namespaced modal. Defaults to '' suffix for
            // backward-compat with pre-v1.29.22 payloads that lack pageName.
            const suffix = mrcatzModalSuffix(data.pageName);
            if (data.status) {
                if (isFullScreen) {
                    // Close the full-page form by flipping the server flag
                    // (which also dispatches mrcatz-form-page-closed for
                    // the scroll-restore hook above).
                    $wire.closeFormPage();
                } else {
                    document.getElementById('modal-data' + suffix)?.close();
                }
                document.getElementById('modal-data-delete' + suffix)?.close();
                $wire.dispatch('refreshDataTable');
                $wire.dispatch('notice', { type: 'success', text: data.text });
            } else {
                $wire.dispatch('notice', { type: 'warning', text: data.text });
            }
        });

        $wire.on('delete-data', (d) => {
            // Payload shape: [data, pageName]. Old single-arg dispatches keep
            // d[1] undefined → pageName defaults to 'page'.
            const data = d[0];
            const pageName = d[1] || 'page';
            $wire.dispatch('prepareDeleteData', { data, pageName });
            document.getElementById('modal-data-delete' + mrcatzModalSuffix(pageName))?.showModal();
        });

        $wire.on('show-notif', (d) => {
            let data = d[0];
            const suffix = mrcatzModalSuffix(data.pageName);
            if (isFullScreen) {
                $wire.closeFormPage();
            } else {
                document.getElementById('modal-data' + suffix)?.close();
            }
            document.getElementById('modal-data-delete' + suffix)?.close();
            $wire.dispatch('notice', { type: data.type, text: data.text });
        });

        $wire.on('open-export-modal', (d) => {
            // Payload shape: [pageName] (string) so pages hosting multiple
            // datatables open the correct `modal-export-<pageName>` dialog.
            // Old single-arg dispatches keep d[0] undefined → '' suffix.
            const pageName = d[0] || 'page';
            document.getElementById('modal-export' + mrcatzModalSuffix(pageName))?.showModal();
        });
    </script>
    @endscript
@endpush
