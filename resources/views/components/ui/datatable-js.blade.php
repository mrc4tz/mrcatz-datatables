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
    editFocused(el) { let d = this.getRowData(el); if (d) $wire.editData(d); },
    deleteFocused(el) { let d = this.getRowData(el); if (d) $wire.deleteData(d); },

    startResize(e, th) {
        const startX = e.pageX;
        const startWidth = th.offsetWidth;
        const move = (e) => {
            const w = Math.max(50, startWidth + e.pageX - startX);
            th.style.width = w + 'px';
            th.style.minWidth = w + 'px';
        };
        const up = () => {
            document.removeEventListener('mousemove', move);
            document.removeEventListener('mouseup', up);
        };
        document.addEventListener('mousemove', move);
        document.addEventListener('mouseup', up);
    },

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

}" x-init="initPresets()">
    @php
        $activeFilterCount = collect($activeFilters ?? [])->filter(fn($f) => !empty($f['value']))->count();
        $bulkEnabled = $bulkPrimaryKey !== null;
        $bulkShow = $bulkEnabled && (!$showBulkButton || $bulkActive);
        $colOrder = !empty($columnOrder) ? $columnOrder : range(0, $posts->countColumn() - 1);
        $totalCols = $posts->countColumn();
        $showExpand = $expandableRows && $posts->hasExpand();
        $totalColspan = $totalCols + ($bulkShow ? 1 : 0) + ($showExpand ? 1 : 0);
    @endphp

    {{-- Toolbar --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
        <div class="flex items-center gap-2 w-full md:w-auto">
            @if($showSearch)
                <form class="flex-1 md:flex-none" wire:submit="searchData">
                    <label class="input input-bordered input-sm md:input-md flex items-center gap-2 w-full md:w-72 transition-all duration-200 focus-within:input-primary focus-within:shadow-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-4 w-4 text-base-content/40">
                            <path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd"/>
                        </svg>
                        <input type="text" class="grow text-sm" placeholder="{{ mrcatz_lang('search_placeholder') }}" wire:model="search"
                               @if($typeSearchWithDelay)
                                   x-data @input.debounce.{{$typeSearchDelay}}="$dispatch('search-typing', { value: $event.target.value })"
                               @elseif($typeSearch)
                                   x-data @input="$dispatch('search-typing', { value: $event.target.value })"
                               @endif/>
                    </label>
                </form>
            @endif

            @if(count($filters) > 0)
                <label class="btn btn-sm md:btn-md btn-square btn-primary swap swap-rotate tooltip tooltip-bottom" data-tip="Filter">
                    <input type="checkbox" x-on:change="open = ! open"/>
                    <span class="material-icons swap-off text-lg">tune</span>
                    <span class="material-icons swap-on text-lg">close</span>
                </label>
                @if($activeFilterCount > 0)
                    <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 rounded-xl bg-base-200/40">
                        <span class="material-icons text-base-content/40 text-sm">filter_alt</span>
                        <p class="text-sm text-base-content/60">
                            <span class="font-semibold text-base-content">{{ $activeFilterCount }}</span> {{ mrcatz_lang('filter_active') }}
                        </p>
                    </div>
                @endif
            @endif

            @if(count($filters) > 0 || $showSearch)
                <div class="relative hidden sm:block">
                    <button class="btn btn-sm md:btn-md btn-ghost btn-square border border-base-content/15 tooltip tooltip-bottom" data-tip="{{ mrcatz_lang('filter_preset') }}"
                            @click="presetOpen = !presetOpen">
                        <span class="material-icons text-lg">bookmarks</span>
                    </button>
                    <div x-show="presetOpen" @click.outside="presetOpen = false"
                         x-transition:enter="transition ease-out duration-150"
                         x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-100"
                         x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                         class="absolute right-0 top-full mt-2 w-64 bg-base-100 border border-base-content/10 rounded-xl shadow-xl z-50 p-3 space-y-2">
                        <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide">{{ mrcatz_lang('filter_preset') }}</p>
                        <template x-if="presets.length === 0">
                            <p class="text-xs text-base-content/30 italic py-2">{{ mrcatz_lang('filter_no_preset') }}</p>
                        </template>
                        <template x-for="(p, i) in presets" :key="i">
                            <div class="flex items-center justify-between gap-2 px-2 py-1.5 rounded-lg hover:bg-base-200/50 cursor-pointer group">
                                <button class="text-sm text-base-content/70 truncate flex-1 text-left" @click="loadPreset(p)" x-text="p.name"></button>
                                <button class="material-icons text-xs text-base-content/20 group-hover:text-error transition-colors" @click.stop="deletePreset(i)">close</button>
                            </div>
                        </template>
                        <div class="border-t border-base-content/10 pt-2 mt-2">
                            <div class="flex gap-1">
                                <input type="text" class="input input-bordered input-xs flex-1 text-xs" placeholder="{{ mrcatz_lang('filter_preset_placeholder') }}"
                                       x-model="presetName" @keydown.enter.prevent="savePreset()"/>
                                <button class="btn btn-xs btn-primary btn-square" @click="savePreset()">
                                    <span class="material-icons text-xs">save</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center gap-2 justify-end">
            @if($showAddButton)
                <button class="btn btn-sm md:btn-md btn-primary gap-2 shadow-sm" wire:click="addData()">
                    <span class="material-icons text-lg">add</span>
                    <span class="hidden sm:inline">{{ mrcatz_lang('btn_add') }}</span>
                </button>
            @endif
            @if($bulkEnabled && $showBulkButton)
                <button class="btn btn-sm md:btn-md gap-1 {{ $bulkActive ? 'btn-secondary' : 'btn-ghost border border-base-content/15' }}"
                        wire:click="toggleBulk">
                    <span class="material-icons text-lg">{{ $bulkActive ? 'check_box' : 'check_box_outline_blank' }}</span>
                    <span class="hidden sm:inline text-sm">{{ mrcatz_lang('btn_select') }}</span>
                </button>
            @endif
            @if($showExportButton)
                <button class="btn btn-sm md:btn-md btn-ghost border border-base-content/15 gap-1"
                        wire:click="openExportModal">
                    <span class="material-icons text-lg">download</span>
                    <span class="hidden sm:inline text-sm">{{ mrcatz_lang('btn_export') }}</span>
                </button>
            @endif
            @if(count($filters) > 0 || $showSearch)
                <button class="btn btn-sm md:btn-md btn-ghost btn-square border border-base-content/15 tooltip tooltip-bottom" data-tip="{{ mrcatz_lang('btn_reset') }}"
                        x-on:click="
                            if ($wire.search || $wire.activeFilters.filter(f => f.value != null).length > 0) {
                                document.getElementById('modal-reset-confirm')?.showModal()
                            } else {
                                $wire.resetData()
                            }
                        ">
                    <span class="material-icons text-lg">restart_alt</span>
                </button>
            @endif
        </div>
    </div>

    @include('mrcatz::components.ui.datatable-filter')

    @if($bulkShow && count($selectedRows) > 0)
        <div class="mb-4 px-3 py-2 md:px-4 md:py-2.5 rounded-xl bg-primary/5 border border-primary/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="material-icons text-primary text-sm">check_circle</span>
                <span class="text-sm font-medium text-primary">{{ count($selectedRows) }} {{ mrcatz_lang('data_selected') }}</span>
            </div>
            <div class="flex gap-2">
                <button class="btn btn-xs btn-error btn-outline gap-1 flex-1 sm:flex-none"
                        x-on:click="document.getElementById('modal-bulk-delete')?.showModal()">
                    <span class="material-icons text-xs">delete</span>
                    {{ mrcatz_lang('btn_delete') }}
                </button>
                <button class="btn btn-xs btn-ghost gap-1 flex-1 sm:flex-none" wire:click="clearSelection">
                    <span class="material-icons text-xs">close</span>
                    {{ mrcatz_lang('btn_cancel') }}
                </button>
            </div>
        </div>
    @endif

    <div class="@if($cardContainer) card shadow-md @endif @if($borderContainer) border rounded-xl border-base-content/10 @endif bg-base-100 w-full overflow-hidden">
        <div @if($cardContainer) class="card-body p-0" @endif>

            @if($posts->hasData())
                <div class="overflow-x-auto">
                    <table class="table outline-none" role="grid" aria-label="{{ $tableTitle ?: $title ?: 'Data table' }}"
                           @if($enableKeyboardNav)
                           tabindex="0"
                           @keydown.arrow-up.prevent="navUp(); $el.querySelectorAll('tbody tr')[focusedRow]?.scrollIntoView({block:'nearest'})"
                           @keydown.arrow-down.prevent="navDown(); $el.querySelectorAll('tbody tr')[focusedRow]?.scrollIntoView({block:'nearest'})"
                           @keydown.escape.prevent="focusedRow = -1"
                           @keydown.enter.prevent="editFocused($el)"
                           @keydown.delete.prevent="deleteFocused($el)"
                           @keydown.backspace.prevent="deleteFocused($el)"
                           @endif>
                        <thead>
                        <tr class="bg-base-200/50 border-b border-base-content/10">
                            @if($showExpand)
                                <th class="w-8"></th>
                            @endif
                            @if($bulkShow)
                                <th class="w-10 text-center">
                                    <input type="checkbox" class="checkbox checkbox-sm checkbox-primary"
                                           aria-label="{{ mrcatz_lang('btn_select') }} all"
                                           @checked($selectAll)
                                           wire:click="toggleSelectAll"/>
                                </th>
                            @endif

                            @foreach($colOrder as $pos => $ci)
                                <th class="text-xs font-semibold uppercase tracking-wider text-base-content/50 relative
                                    @if($posts->gravity($ci)=='center') text-center
                                    @elseif($posts->gravity($ci)=='right') text-right
                                    @else text-left @endif"
                                    @if($posts->getOrder($ci) === 'asc') aria-sort="ascending"
                                    @elseif($posts->getOrder($ci) === 'desc') aria-sort="descending"
                                    @elseif($posts->getSort($ci) && $posts->getKey($ci)) aria-sort="none"
                                    @endif
                                    @if($enableColumnReorder)
                                    draggable="true"
                                    @dragstart="dragCol = {{ $pos }}"
                                    @dragover.prevent="dragOverCol = {{ $pos }}"
                                    @drop.prevent="$wire.reorderColumn(dragCol, {{ $pos }}, {{ $totalCols }}); dragCol = -1; dragOverCol = -1"
                                    @dragend="dragCol = -1; dragOverCol = -1"
                                    :style="dragOverCol === {{ $pos }} && dragCol !== {{ $pos }} && dragCol >= 0 ? 'box-shadow: inset 3px 0 0 0 var(--color-primary)' : ''"
                                    @endif>
                                    @if($enableColumnSorting && $posts->getKey($ci) != null && $posts->getSort($ci))
                                        <button class="flex items-center gap-1 hover:text-primary transition-colors duration-200"
                                                wire:click="orderData({{ json_encode($posts->getKey($ci)) }}, {{ json_encode($posts->getOrder($ci)) }})">
                                            {{ $posts->getHead($ci) }}
                                            @if($posts->getOrder($ci) === 'asc')
                                                <span class="material-icons text-sm text-primary/50">keyboard_arrow_up</span>
                                            @elseif($posts->getOrder($ci) === 'desc')
                                                <span class="material-icons text-sm text-primary/50">keyboard_arrow_down</span>
                                            @else
                                                <span class="material-symbols-outlined text-sm opacity-40">unfold_more</span>
                                            @endif
                                        </button>
                                    @else
                                        @if($posts->getIndex($ci) != null || $posts->getKey($ci) != null)
                                            {{ $posts->getHead($ci) }}
                                        @else
                                            <span class="block text-center">{{ $posts->getHead($ci) }}</span>
                                        @endif
                                    @endif

                                    @if($enableColumnResize && $posts->getKey($ci) != null)
                                        <div style="position:absolute;right:-2px;top:25%;bottom:25%;width:12px;cursor:col-resize;z-index:10;display:flex;align-items:center;justify-content:center;"
                                             @mousedown.prevent.stop="startResize($event, $el.parentElement)"
                                             onmouseenter="this.firstElementChild.style.opacity='1'"
                                             onmouseleave="this.firstElementChild.style.opacity='0'">
                                            <div style="width:3px;height:100%;border-radius:9999px;background:linear-gradient(to bottom,transparent,var(--color-primary),transparent);opacity:0;transition:opacity 0.2s;"></div>
                                        </div>
                                    @endif
                                </th>
                            @endforeach
                        </tr>
                        </thead>
                        <tbody>
                        @for($i = 0; $i < $posts->countRow(); $i++)
                            <tr class="border-b border-base-content/5 transition-colors duration-150 cursor-pointer"
                                :style="focusedRow === {{ $i }} ? 'background:color-mix(in srgb,var(--color-primary) 25%,transparent)' : '{{ $tableZebraStyle && $i % 2 === 1 ? 'background:color-mix(in srgb,var(--color-base-content) 3%,transparent)' : '' }}'"
                                @click="focusedRow = {{ $i }}"
                                data-row="{{ json_encode($posts->getRowRawData($i)) }}">

                                @if($showExpand)
                                    <td class="w-8 text-center" @click.stop="toggleExpand({{ $i }})">
                                        <span class="material-icons text-sm text-base-content/40 transition-transform duration-200"
                                              :class="expandedRows.includes({{ $i }}) ? 'rotate-90' : ''">chevron_right</span>
                                    </td>
                                @endif

                                @if($bulkShow)
                                    <td class="w-10 text-center" @click.stop>
                                        @if($posts->isBulkEnabled($i))
                                            <input type="checkbox" class="checkbox checkbox-sm checkbox-primary"
                                                   aria-label="{{ mrcatz_lang('btn_select') }} row {{ $i + 1 }}"
                                                   value="{{ $posts->getRowRawData($i)->{$bulkPrimaryKey} }}"
                                                   wire:model.live="selectedRows"/>
                                        @endif
                                    </td>
                                @endif

                                @foreach($colOrder as $ci)
                                    @if($posts->isTH($ci))
                                        <th class="text-sm @if($posts->isUppercase($ci)) uppercase @endif
                                            @if($posts->gravity($ci)=='center') text-center
                                            @elseif($posts->gravity($ci)=='right') text-right
                                            @else text-left @endif">{!! $posts->getData($i, $ci) !!}</th>
                                    @else
                                        <td class="text-sm @if($posts->isUppercase($ci)) uppercase @endif
                                            @if($posts->gravity($ci)=='center') text-center
                                            @elseif($posts->gravity($ci)=='right') text-right
                                            @else text-left @endif">{!! $posts->getData($i, $ci) !!}</td>
                                    @endif
                                @endforeach
                            </tr>
                            @if($showExpand)
                                <tr x-show="expandedRows.includes({{ $i }})" class="bg-base-200/20">
                                    <td colspan="{{ $totalColspan }}" class="p-0">
                                        <div class="px-6 py-4 text-sm" x-show="expandedRows.includes({{ $i }})"
                                             x-transition:enter="transition ease-out duration-200"
                                             x-transition:enter-start="opacity-0 -translate-y-2"
                                             x-transition:enter-end="opacity-100 translate-y-0">
                                            {!! $posts->getExpandContent($i) !!}
                                        </div>
                                    </td>
                                </tr>
                            @endif
                        @endfor
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-20 px-4">
                    <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mb-4">
                        @if(!empty($search) || $activeFilterCount > 0)
                            <span class="material-icons text-base-content/30 text-3xl">search_off</span>
                        @else
                            <span class="material-icons text-base-content/30 text-3xl">inbox</span>
                        @endif
                    </div>
                    @if(!empty($search))
                        <p class="text-base-content/40 text-sm font-medium">{{ mrcatz_lang('no_results_for', [':query' => $search]) }}</p>
                        <p class="text-base-content/25 text-xs mt-1">{{ mrcatz_lang('no_results_hint') }}</p>
                    @elseif($activeFilterCount > 0)
                        <p class="text-base-content/40 text-sm font-medium">{{ mrcatz_lang('no_results') }}</p>
                        <p class="text-base-content/25 text-xs mt-1">{{ mrcatz_lang('no_results_filter_hint') }}</p>
                    @else
                        <p class="text-base-content/40 text-sm font-medium">{{ mrcatz_lang('no_data') }}</p>
                        <p class="text-base-content/25 text-xs mt-1">{{ mrcatz_lang('no_data_hint') }}</p>
                    @endif
                </div>
            @endif

            <div class="flex items-center justify-center py-20" wire:target="datatables" wire:loading>
                <span class="loading loading-spinner loading-lg text-primary"></span>
            </div>

            @if($usePagination)
                <div class="px-4 py-3 border-t border-base-content/5 @if($borderContainer) p-4 @endif">
                    {{ $posts->links('mrcatz::components.ui.pagination') }}
                </div>
            @endif
        </div>
    </div>

    @if($showKeyboardNavNote && $enableKeyboardNav && $posts->hasData())
        <div class="mt-2 flex items-center justify-center gap-3 flex-wrap" style="font-size:10px;color:oklch(var(--bc)/.15);">
            <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">&#8593;&#8595;</kbd> {{ mrcatz_lang('key_navigate') }}</span>
            <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">Enter</kbd> {{ mrcatz_lang('key_edit') }}</span>
            <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">Del/&#9003;</kbd> {{ mrcatz_lang('key_delete') }}</span>
            <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">Esc</kbd> {{ mrcatz_lang('key_cancel') }}</span>
        </div>
    @endif

    @if($showExportButton)
        <dialog id="modal-export" class="modal modal-bottom sm:modal-middle" wire:ignore.self x-data="{ format: 'excel', scope: 'filtered' }" aria-modal="true" aria-labelledby="modal-export-title">
            <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-lg" x-trap.noscroll="document.getElementById('modal-export')?.open">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-base-content/10">
                    <h3 id="modal-export-title" class="text-lg font-bold text-base-content flex items-center gap-2">
                        <span class="material-icons text-primary">download</span>
                        {{ mrcatz_lang('export_title') }}
                    </h3>
                    <form method="dialog">
                        <button class="btn btn-ghost btn-sm btn-circle hover:bg-base-200"><span class="material-icons">close</span></button>
                    </form>
                </div>

                <div class="max-h-[65vh] overflow-y-auto pr-1 -mr-1 space-y-5">
                    <div>
                        <label class="text-xs font-semibold text-base-content/60 uppercase tracking-wide mb-2 block">{{ mrcatz_lang('export_format') }}</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="format === 'excel' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                                   @click="format = 'excel'">
                                <span class="material-icons text-success text-2xl">table_view</span>
                                <div><p class="text-sm font-semibold text-base-content">Excel</p><p class="text-xs text-base-content/40">.xlsx</p></div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="format === 'pdf' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                                   @click="format = 'pdf'">
                                <span class="material-icons text-error text-2xl">picture_as_pdf</span>
                                <div><p class="text-sm font-semibold text-base-content">PDF</p><p class="text-xs text-base-content/40">.pdf</p></div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="text-xs font-semibold text-base-content/60 uppercase tracking-wide mb-2 block">{{ mrcatz_lang('export_scope') }}</label>
                        <div class="grid grid-cols-2 gap-2">
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="scope === 'all' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                                   @click="scope = 'all'; $wire.updateExportCount('all')">
                                <span class="material-icons text-lg" :class="scope === 'all' ? 'text-primary' : 'text-base-content/30'">select_all</span>
                                <div><p class="text-sm font-semibold text-base-content">{{ mrcatz_lang('export_all') }}</p><p class="text-xs text-base-content/40">{{ mrcatz_lang('export_all_desc') }}</p></div>
                            </label>
                            <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                                   :class="scope === 'filtered' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                                   @click="scope = 'filtered'; $wire.updateExportCount('filtered')">
                                <span class="material-icons text-lg" :class="scope === 'filtered' ? 'text-primary' : 'text-base-content/30'">filter_alt</span>
                                <div><p class="text-sm font-semibold text-base-content">{{ mrcatz_lang('export_filtered') }}</p><p class="text-xs text-base-content/40">{{ mrcatz_lang('export_filtered_desc') }}</p></div>
                            </label>
                        </div>
                    </div>

                    <div x-show="scope === 'filtered'" x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="rounded-xl bg-base-200/40 p-4 space-y-3">
                            <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide flex items-center gap-1">
                                <span class="material-icons text-xs">tune</span>
                                {{ mrcatz_lang('export_settings') }}
                            </p>
                            <div>
                                <label class="text-xs text-base-content/50 mb-1 block">{{ mrcatz_lang('export_search') }}</label>
                                <label class="input input-bordered input-sm flex items-center gap-2 w-full focus-within:input-primary transition-all">
                                    <span class="material-icons text-base-content/30 text-sm">search</span>
                                    <input type="text" class="grow text-sm" placeholder="{{ mrcatz_lang('export_search_placeholder') }}" wire:model="exportSearch"/>
                                </label>
                            </div>
                            @foreach($dataFilters as $f => $filter)
                                @if($filterShow[$f] ?? true)
                                    <div>
                                        <label class="text-xs text-base-content/50 mb-1 block">{{ $filter['label'] }}</label>
                                        <select class="select select-bordered select-sm w-full text-sm focus:select-primary transition-all"
                                                wire:model="exportFilterValues.{{ $filter['id'] }}">
                                            <option value="{{ $default_filter_value }}">{{ mrcatz_lang('filter_all') }}</option>
                                            @foreach($filterData[$f] as $data)
                                                <option value="{{ $data[$filter['value']] }}">{{ $data[$filter['option']] }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            @endforeach
                            @if(count($dataFilters) === 0)
                                <p class="text-xs text-base-content/40 italic py-1">{{ mrcatz_lang('filter_no_available') }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="mt-4 p-3 rounded-xl bg-base-200/40 flex items-center gap-2">
                    <span class="material-icons text-base-content/40 text-sm">info</span>
                    <p class="text-sm text-base-content/60">
                        <span class="font-semibold text-base-content" wire:loading.remove wire:target="updateExportCount">{{ number_format($exportCount) }}</span>
                        <span class="loading loading-spinner loading-xs" wire:loading wire:target="updateExportCount"></span>
                        {{ mrcatz_lang('export_count') }}
                    </p>
                </div>

                <div class="modal-action pt-4 mt-4 border-t border-base-content/10">
                    <button class="btn btn-primary gap-2 px-6 shadow-sm"
                            x-on:click="$wire.exportData(format, scope); document.getElementById('modal-export').close();">
                        <span class="material-icons text-lg">download</span>
                        {{ mrcatz_lang('btn_export') }}
                    </button>
                    <form method="dialog">
                        <button class="btn btn-ghost">{{ mrcatz_lang('btn_cancel') }}</button>
                    </form>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>
    @endif

    <dialog id="modal-reset-confirm" class="modal modal-bottom sm:modal-middle" aria-modal="true" aria-labelledby="modal-reset-title">
        <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-sm text-center" x-data x-trap.noscroll="document.getElementById('modal-reset-confirm')?.open">
            <div class="w-14 h-14 rounded-full bg-warning/10 flex items-center justify-center mx-auto mb-4">
                <span class="material-icons text-warning text-2xl">restart_alt</span>
            </div>
            <h3 id="modal-reset-title" class="text-base font-bold text-base-content mb-1">{{ mrcatz_lang('reset_title') }}</h3>
            <p class="text-sm text-base-content/50 mb-6">{{ mrcatz_lang('reset_desc') }}</p>
            <div class="flex gap-2 justify-center">
                <form method="dialog"><button class="btn btn-ghost btn-sm">{{ mrcatz_lang('btn_cancel') }}</button></form>
                <button class="btn btn-warning btn-sm"
                        x-on:click="$wire.resetData(); document.getElementById('modal-reset-confirm')?.close();">
                    {{ mrcatz_lang('btn_yes_reset') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    @if($bulkPrimaryKey !== null)
        <dialog id="modal-bulk-delete" class="modal modal-bottom sm:modal-middle" wire:ignore.self aria-modal="true" aria-labelledby="modal-bulk-delete-title">
            <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-sm text-center" x-data x-trap.noscroll="document.getElementById('modal-bulk-delete')?.open">
                <div class="w-14 h-14 rounded-full bg-error/10 flex items-center justify-center mx-auto mb-4">
                    <span class="material-icons text-error text-2xl">delete_sweep</span>
                </div>
                <h3 id="modal-bulk-delete-title" class="text-base font-bold text-base-content mb-1">{{ mrcatz_lang('bulk_delete_title') }}</h3>
                <p class="text-sm text-base-content/50 mb-6">{{ mrcatz_lang('bulk_delete_desc') }}</p>
                <div class="flex gap-2 justify-center">
                    <form method="dialog"><button class="btn btn-ghost btn-sm">{{ mrcatz_lang('btn_cancel') }}</button></form>
                    <button class="btn btn-error btn-sm"
                            x-on:click="$wire.bulkDelete(); document.getElementById('modal-bulk-delete')?.close();">
                        {{ mrcatz_lang('btn_yes_delete') }}
                    </button>
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>
    @endif

    @if($withLoading)
        @if($load_start)
            <div class="fixed inset-0 z-50 flex items-center justify-center bg-base-content/20 backdrop-blur-sm">
                <div class="bg-base-100 rounded-2xl p-6 shadow-xl flex items-center gap-3">
                    <span class="loading loading-spinner loading-md text-primary"></span>
                    <span class="text-sm text-base-content/70">{{ mrcatz_lang('loading') }}</span>
                </div>
            </div>
        @endif
        <div wire:loading wire:target="showLoading, searchData, goToP, nextPage, previousPage, change, paginate, saveData, dropData, resetData, orderData, editData, deleteData, exportData, bulkDelete"
             class="fixed inset-0 z-50 flex items-center justify-center bg-base-content/20 backdrop-blur-sm">
            <div class="bg-base-100 rounded-2xl p-6 shadow-xl flex items-center gap-3">
                <span class="loading loading-spinner loading-md text-primary"></span>
                <span class="text-sm text-base-content/70">{{ mrcatz_lang('processing') }}</span>
            </div>
        </div>
    @endif
</div>
