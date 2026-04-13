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

        // When $modalFullScreen is on, the Livewire listener flips
        // $formPageVisible server-side which re-renders the component as
        // a full-page form — no dialog to open, no modal API to call.
        const isFullScreen = () => !!$wire.modalFullScreen;

        $wire.on('add-data', () => {
            $wire.dispatch('prepareAddData');
            if (!isFullScreen()) document.getElementById('modal-data')?.showModal();
        });

        $wire.on('edit-data', (d) => {
            $wire.dispatch('prepareEditData', { data: d[0] });
            if (!isFullScreen()) document.getElementById('modal-data')?.showModal();
        });

        $wire.on('refresh-data', (d) => {
            let data = d[0];
            if (data.status) {
                if (isFullScreen()) {
                    // Close the full-page form by flipping the server flag.
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
            if (isFullScreen() && $wire.formPageVisible) {
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
