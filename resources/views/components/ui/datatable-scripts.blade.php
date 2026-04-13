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
        // Scroll the page component's root back into view so users land
        // at the top of the table again. Smooth scroll respects reduced-
        // motion preferences automatically.
        const scrollPageToTop = () => {
            if (!isFullScreen) return;
            const root = $wire.$el || document.querySelector('[wire\\:id]');
            if (root && typeof root.scrollIntoView === 'function') {
                root.scrollIntoView({ behavior: 'smooth', block: 'start' });
            } else {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        };

        $wire.on('add-data', () => {
            $wire.dispatch('prepareAddData');
            if (!isFullScreen) document.getElementById('modal-data')?.showModal();
        });

        $wire.on('edit-data', (d) => {
            $wire.dispatch('prepareEditData', { data: d[0] });
            if (!isFullScreen) document.getElementById('modal-data')?.showModal();
        });

        // All close paths (Cancel / Close button clicks, save success)
        // funnel through closeFormPage() on the server, which dispatches
        // this event. Scroll the datatable back into view on each close.
        $wire.on('mrcatz-form-page-closed', scrollPageToTop);

        $wire.on('refresh-data', (d) => {
            let data = d[0];
            if (data.status) {
                if (isFullScreen) {
                    // Close the full-page form by flipping the server flag
                    // (which also dispatches mrcatz-form-page-closed for
                    // the scroll-restore hook above).
                    $wire.closeFormPage();
                } else {
                    document.getElementById('modal-data')?.close();
                }
                document.getElementById('modal-data-delete')?.close();
                $wire.dispatch('refreshDataTable');
                $wire.dispatch('notice', { type: 'success', text: data.text });
            } else {
                $wire.dispatch('notice', { type: 'warning', text: data.text });
            }
        });

        $wire.on('delete-data', (d) => {
            $wire.dispatch('prepareDeleteData', { data: d[0] });
            document.getElementById('modal-data-delete')?.showModal();
        });

        $wire.on('show-notif', (d) => {
            let data = d[0];
            if (isFullScreen) {
                $wire.closeFormPage();
            } else {
                document.getElementById('modal-data')?.close();
            }
            document.getElementById('modal-data-delete')?.close();
            $wire.dispatch('notice', { type: data.type, text: data.text });
        });

        $wire.on('open-export-modal', () => {
            document.getElementById('modal-export')?.showModal();
        });
    </script>
    @endscript
@endpush
