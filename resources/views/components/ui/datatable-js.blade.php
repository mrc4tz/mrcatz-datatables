<div wire:init="showLoading" x-data="{
    open: false,
    focusedRow: -1,
    maxRows: {{ $posts->hasData() ? $posts->countRow() : 0 }},
    dragCol: -1,
    dragOverCol: -1,
    expandedRows: [],
    toggleExpand(i) {
        const idx = this.expandedRows.indexOf(i);
        if (idx >= 0) this.expandedRows.splice(idx, 1);
        else this.expandedRows.push(i);
    },

    navUp() { if (this.focusedRow > 0) this.focusedRow-- },
    navDown() { if (this.focusedRow < this.maxRows - 1) this.focusedRow++ },

    getRowData(el) {
        let rows = el.querySelectorAll('tbody tr[data-row]');
        return (this.focusedRow >= 0 && rows[this.focusedRow])
            ? JSON.parse(rows[this.focusedRow].dataset.row) : null;
    },
    editFocused(el, e) {
        if (e && e.target.tagName === 'INPUT') return;
        let d = this.getRowData(el); if (d) $wire.editData(d);
    },
    deleteFocused(el) { let d = this.getRowData(el); if (d) $wire.deleteData(d); },

    colWidths: {},
    startResize(e, th, colIdx) {
        const startX = e.pageX;
        const startWidth = th.offsetWidth;
        const self = this;
        const move = (e) => {
            const w = Math.max(50, startWidth + e.pageX - startX);
            th.style.width = w + 'px';
            th.style.minWidth = w + 'px';
        };
        const up = () => {
            document.removeEventListener('mousemove', move);
            document.removeEventListener('mouseup', up);
            self.colWidths[colIdx] = th.offsetWidth;
            $wire.columnWidths = Object.assign({}, self.colWidths);
        };
        document.addEventListener('mousemove', move);
        document.addEventListener('mouseup', up);
    },
    getColWidth(ci) {
        return this.colWidths[ci] ? this.colWidths[ci] + 'px' : null;
    },

    colVisOpen: false,
    presetOpen: false,
    presets: [],
    presetName: '',
    presetKey: 'mrcatz_presets_' + window.location.pathname,
    _storageAvailable: false,
    _checkStorage() {
        try { const k = '__mrcatz_test__'; localStorage.setItem(k, '1'); localStorage.removeItem(k); return true; }
        catch(e) { return false; }
    },
    initPresets() {
        this._storageAvailable = this._checkStorage();
        if (this._storageAvailable) { this.presets = JSON.parse(localStorage.getItem(this.presetKey) || '[]'); }
    },
    savePreset() {
        if (!this.presetName.trim()) return;
        this.presets.push({ name: this.presetName.trim(), url: window.location.search });
        if (this._storageAvailable) { localStorage.setItem(this.presetKey, JSON.stringify(this.presets)); }
        this.presetName = '';
    },
    loadPreset(p) { window.location.search = p.url; },
    deletePreset(i) {
        this.presets.splice(i, 1);
        if (this._storageAvailable) { localStorage.setItem(this.presetKey, JSON.stringify(this.presets)); }
    },

}" x-init="initPresets(); colWidths = Object.assign({}, $wire.columnWidths || {})">
    @php
        $activeFilterCount = collect($activeFilters ?? [])->filter(fn($f) => !empty($f['value']))->count();
        $bulkEnabled = $bulkPrimaryKey !== null;
        $bulkShow = $bulkEnabled && (!$showBulkButton || $bulkActive);
        $colOrder = !empty($columnOrder) ? $columnOrder : range(0, $posts->countColumn() - 1);
        $visibleColOrder = $enableColumnVisibility
            ? array_values(array_filter($colOrder, fn($ci) => !in_array($ci, $hiddenColumns ?? [])))
            : $colOrder;
        $totalCols = $posts->countColumn();
        $expandMode = $expandableRows === true ? 'both' : ($expandableRows ?: false);
        $hasExpand = $expandMode && $posts->hasExpand();
        $showExpandMobile = $hasExpand && in_array($expandMode, ['both', 'mobile']);
        $showExpandDesktop = $hasExpand && in_array($expandMode, ['both', 'desktop']);
        $showExpand = $showExpandDesktop; // for desktop table colspan
        $totalColspan = $totalCols + ($bulkShow ? 1 : 0) + ($showExpand ? 1 : 0);
    @endphp

    @include('mrcatz::components.ui.partials.toolbar')

    <div class="@if($cardContainer) md:card md:shadow-md @endif @if($borderContainer) md:border md:rounded-xl md:border-base-content/10 @endif md:bg-base-100 w-full md:overflow-hidden">
        <div @if($cardContainer) class="md:card-body md:p-0" @endif>
            @include('mrcatz::components.ui.partials.table-content')
        </div>
    </div>

    @include('mrcatz::components.ui.partials.modals')
</div>
