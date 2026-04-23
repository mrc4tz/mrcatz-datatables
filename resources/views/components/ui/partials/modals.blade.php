{{-- Keyboard hints --}}
@if($showKeyboardNavNote && $enableKeyboardNav && $posts->hasData())
    <div class="mt-2 flex items-center justify-center gap-3 flex-wrap" style="font-size:10px;color:oklch(var(--bc)/.15);">
        <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">&#8593;&#8595;</kbd> {{ mrcatz_lang('key_navigate') }}</span>
        @if($posts->hasEditAction)
            <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">Enter</kbd> {{ mrcatz_lang('key_edit') }}</span>
        @endif
        @if($posts->hasDeleteAction)
            <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">Del/&#9003;</kbd> {{ mrcatz_lang('key_delete') }}</span>
        @endif
        <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">Esc</kbd> {{ mrcatz_lang('key_cancel') }}</span>
    </div>
@endif

@php
    // v1.29.22+ — all modals rendered by this file (export, bulk-delete,
    // mobile-columns, mobile-preset, reset-confirm) suffix their DOM ids
    // with `setPageName()` when it's non-default so two tables on one page
    // (multi-CRUD) don't collide on identical dialog elements. `$_ms` stays
    // '' for single-CRUD pages — byte-identical to pre-v1.29.22 ids.
    $_ms = $this->setPageName() === 'page' ? '' : '-' . $this->setPageName();
    $exportModalId = 'modal-export' . $_ms;
@endphp

{{-- Export modal --}}
@if($showExportButton)
    <dialog id="{{ $exportModalId }}" class="modal modal-bottom sm:modal-middle" wire:ignore.self
            x-data="{ format: 'pdf', scope: 'filtered', includeConditions: true, get hasConditions() { return ($wire.exportPreview || []).length > 0 } }"
            aria-modal="true" aria-labelledby="{{ $exportModalId }}-title">
        <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-lg" x-trap.noscroll="document.getElementById('{{ $exportModalId }}')?.open">
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-base-content/10">
                <h3 id="{{ $exportModalId }}-title" class="text-lg font-bold text-base-content flex items-center gap-2">
                    {!! mrcatz_icon('download', 'text-primary') !!}
                    {{ mrcatz_lang('export_title') }}
                </h3>
                <form method="dialog">
                    <button class="btn btn-ghost btn-sm btn-circle hover:bg-base-200">{!! mrcatz_icon('close') !!}</button>
                </form>
            </div>

            <div class="max-h-[65vh] overflow-y-auto pr-1 -mr-1 space-y-5">
                <div>
                    <label class="text-xs font-semibold text-base-content/60 uppercase tracking-wide mb-2 block">{{ mrcatz_lang('export_format') }}</label>
                    <div class="grid grid-cols-3 gap-2">
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="format === 'pdf' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                               @click="format = 'pdf'">
                            {!! mrcatz_icon('picture_as_pdf', 'text-2xl text-error') !!}
                            <div><p class="text-sm font-semibold text-base-content">PDF</p><p class="text-xs text-base-content/40">.pdf</p></div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="format === 'csv' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                               @click="format = 'csv'">
                            {!! mrcatz_icon('description', 'text-2xl text-info') !!}
                            <div><p class="text-sm font-semibold text-base-content">CSV</p><p class="text-xs text-base-content/40">.csv</p></div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="format === 'excel' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                               @click="format = 'excel'">
                            {!! mrcatz_icon('table_view', 'text-2xl text-success') !!}
                            <div><p class="text-sm font-semibold text-base-content">Excel</p><p class="text-xs text-base-content/40">.xlsx</p></div>
                        </label>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-base-content/60 uppercase tracking-wide mb-2 block">{{ mrcatz_lang('export_scope') }}</label>
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="scope === 'all' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                               @click="scope = 'all'; $wire.updateExportCount('all')">
                            <span :class="scope === 'all' ? 'text-primary' : 'text-base-content/30'">{!! mrcatz_icon('select_all', 'text-lg') !!}</span>
                            <div><p class="text-sm font-semibold text-base-content">{{ mrcatz_lang('export_all') }}</p><p class="text-xs text-base-content/40">{{ mrcatz_lang('export_all_desc') }}</p></div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="scope === 'filtered' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                               @click="scope = 'filtered'; $wire.updateExportCount('filtered')">
                            <span :class="scope === 'filtered' ? 'text-primary' : 'text-base-content/30'">{!! mrcatz_icon('filter_alt', 'text-lg') !!}</span>
                            <div><p class="text-sm font-semibold text-base-content">{{ mrcatz_lang('export_filtered') }}</p><p class="text-xs text-base-content/40">{{ mrcatz_lang('export_filtered_desc') }}</p></div>
                        </label>
                    </div>
                </div>

                <div x-show="scope === 'filtered'" x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="rounded-xl bg-base-200/40 p-4 space-y-2">
                        <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide flex items-center gap-1">
                            {!! mrcatz_icon('tune', 'text-xs') !!}
                            {{ mrcatz_lang('export_preview_title') }}
                        </p>
                        @forelse($exportPreview as $item)
                            <div class="flex items-center gap-2 py-1">
                                {!! mrcatz_icon($item['icon'], 'text-sm text-base-content/40 shrink-0') !!}
                                <span class="text-xs text-base-content/50 shrink-0">{{ $item['label'] }}</span>
                                <span class="text-xs font-medium text-base-content truncate">{{ $item['value'] }}</span>
                            </div>
                        @empty
                            <p class="text-xs text-base-content/40 italic py-1">{{ mrcatz_lang('export_preview_empty') }}</p>
                        @endforelse

                        <label class="flex items-center gap-2 pt-3 mt-2 border-t border-base-content/5"
                               :class="hasConditions ? 'cursor-pointer' : 'cursor-not-allowed opacity-60'">
                            <input type="checkbox" class="checkbox checkbox-primary checkbox-xs"
                                   x-model="includeConditions"
                                   :disabled="!hasConditions"
                                   :checked="hasConditions && includeConditions" />
                            <span class="flex flex-col leading-tight">
                                <span class="text-xs font-medium text-base-content">{{ mrcatz_lang('export_conditions_toggle') }}</span>
                                <span x-show="!hasConditions" class="text-[10px] text-base-content/40 italic">{{ mrcatz_lang('export_conditions_hint') }}</span>
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 rounded-xl bg-base-200/40 flex items-center gap-2">
                {!! mrcatz_icon('info', 'text-sm text-base-content/40') !!}
                <p class="text-sm text-base-content/60">
                    <span class="font-semibold text-base-content" wire:loading.remove wire:target="updateExportCount">{{ number_format($exportCount) }}</span>
                    <span class="loading loading-spinner loading-xs" wire:loading wire:target="updateExportCount"></span>
                    {{ mrcatz_lang('export_count') }}
                </p>
            </div>

            <div class="modal-action pt-4 mt-4 border-t border-base-content/10">
                <button class="btn btn-primary gap-2 px-6 shadow-sm"
                        x-on:click="$dispatch('mrcatz-export-started', { format: format }); $wire.exportData(format, scope, hasConditions && includeConditions); document.getElementById('{{ $exportModalId }}').close();">
                    {!! mrcatz_icon('download', 'text-lg') !!}
                    {{ mrcatz_lang('btn_export') }}
                </button>
                <form method="dialog">
                    <button class="btn btn-ghost">{{ mrcatz_lang('btn_cancel') }}</button>
                </form>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>

    {{-- Export processing indicator (sticky bottom-right, Drive-style) --}}
    <div wire:loading.flex wire:target="exportData"
         x-data="{
            fmt: 'pdf',
            cfg: {
                pdf:   { icon: 'picture_as_pdf', color: 'error',   label: @js(mrcatz_lang('export_processing_pdf')) },
                csv:   { icon: 'description',    color: 'info',    label: @js(mrcatz_lang('export_processing_csv')) },
                excel: { icon: 'table_view',     color: 'success', label: @js(mrcatz_lang('export_processing_excel')) }
            }
         }"
         x-on:mrcatz-export-started.window="fmt = $event.detail.format"
         class="fixed bottom-6 right-6 z-[60] w-[22rem] max-w-[calc(100vw-2rem)] items-center gap-3 p-4 bg-base-100 border border-base-content/10 rounded-2xl shadow-2xl"
         style="display: none;"
         role="status" aria-live="polite">

        {{-- Icon badge --}}
        <div class="shrink-0 w-11 h-11 rounded-xl flex items-center justify-center relative overflow-hidden"
             :class="{
                'bg-error/10':   fmt === 'pdf',
                'bg-info/10':    fmt === 'csv',
                'bg-success/10': fmt === 'excel'
             }">
            <template x-if="fmt === 'pdf'">{!! mrcatz_icon('picture_as_pdf', 'text-2xl text-error') !!}</template>
            <template x-if="fmt === 'csv'">{!! mrcatz_icon('description', 'text-2xl text-info') !!}</template>
            <template x-if="fmt === 'excel'">{!! mrcatz_icon('table_view', 'text-2xl text-success') !!}</template>
            {{-- Pulsing ring for motion --}}
            <span class="absolute inset-0 rounded-xl animate-ping opacity-30"
                  :class="{
                    'bg-error/20':   fmt === 'pdf',
                    'bg-info/20':    fmt === 'csv',
                    'bg-success/20': fmt === 'excel'
                  }"></span>
        </div>

        {{-- Text column --}}
        <div class="flex-1 min-w-0 leading-tight">
            <p class="text-sm font-semibold text-base-content truncate"
               x-text="@js(mrcatz_lang('export_processing_title')).replace(':format', cfg[fmt].label)"></p>
            <p class="text-xs text-base-content/50 truncate mt-0.5">{{ mrcatz_lang('export_processing_subtitle') }}</p>
        </div>

        {{-- Spinner --}}
        <div class="shrink-0">
            <span class="loading loading-spinner loading-sm"
                  :class="{
                    'text-error':   fmt === 'pdf',
                    'text-info':    fmt === 'csv',
                    'text-success': fmt === 'excel'
                  }"></span>
        </div>
    </div>
@endif

{{-- Reset confirmation modal --}}
<dialog id="modal-reset-confirm{{ $_ms }}" class="modal modal-bottom sm:modal-middle" aria-modal="true" aria-labelledby="modal-reset-title{{ $_ms }}">
    <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-sm text-center" x-data x-trap.noscroll="document.getElementById('modal-reset-confirm{{ $_ms }}')?.open">
        <div class="w-14 h-14 rounded-full bg-warning/10 flex items-center justify-center mx-auto mb-4">
            {!! mrcatz_icon('restart_alt', 'text-2xl text-warning') !!}
        </div>
        <h3 id="modal-reset-title{{ $_ms }}" class="text-base font-bold text-base-content mb-1">{{ mrcatz_lang('reset_title') }}</h3>
        <p class="text-sm text-base-content/50 mb-6">{{ mrcatz_lang('reset_desc') }}</p>
        <div class="flex gap-2 justify-center">
            <form method="dialog"><button class="btn btn-ghost btn-sm">{{ mrcatz_lang('btn_cancel') }}</button></form>
            <button class="btn btn-warning btn-sm"
                    x-on:click="$wire.resetData(); document.getElementById('modal-reset-confirm{{ $_ms }}')?.close();">
                {{ mrcatz_lang('btn_yes_reset') }}
            </button>
        </div>
    </div>
    <form method="dialog" class="modal-backdrop"><button>close</button></form>
</dialog>

{{-- Bulk delete modal --}}
@if($bulkPrimaryKey !== null && (!property_exists($this, 'showBulkDeleteAction') || $this->showBulkDeleteAction))
    <dialog id="modal-bulk-delete{{ $_ms }}" class="modal modal-bottom sm:modal-middle" wire:ignore.self aria-modal="true" aria-labelledby="modal-bulk-delete{{ $_ms }}-title">
        <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-sm text-center" x-data x-trap.noscroll="document.getElementById('modal-bulk-delete{{ $_ms }}')?.open">
            <div class="w-14 h-14 rounded-full bg-error/10 flex items-center justify-center mx-auto mb-4">
                {!! mrcatz_icon('delete_sweep', 'text-2xl text-error') !!}
            </div>
            <h3 id="modal-bulk-delete{{ $_ms }}-title" class="text-base font-bold text-base-content mb-1">{{ mrcatz_lang('bulk_delete_title') }}</h3>
            <p class="text-sm text-base-content/50 mb-6">{{ mrcatz_lang('bulk_delete_desc') }}</p>
            <div class="flex gap-2 justify-center">
                <form method="dialog"><button class="btn btn-ghost btn-sm">{{ mrcatz_lang('btn_cancel') }}</button></form>
                <button class="btn btn-error btn-sm"
                        x-on:click="$wire.bulkDelete(); document.getElementById('modal-bulk-delete{{ $_ms }}')?.close();">
                    {{ mrcatz_lang('btn_yes_delete') }}
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
@endif

{{-- Mobile columns bottom-sheet --}}
@if($enableColumnVisibility)
    <dialog id="modal-mobile-columns{{ $_ms }}" class="modal modal-bottom sm:hidden" aria-modal="true" aria-labelledby="modal-columns-title{{ $_ms }}">
        <div class="modal-box bg-base-100 rounded-t-2xl shadow-2xl max-w-lg p-0">
            <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-base-content/10">
                <h3 id="modal-columns-title{{ $_ms }}" class="text-sm font-bold text-base-content flex items-center gap-2">
                    {!! mrcatz_icon('view_column', 'text-primary') !!}
                    {{ mrcatz_lang('col_visibility') }}
                </h3>
                <button class="btn btn-ghost btn-sm btn-circle hover:bg-base-200"
                        onclick="document.getElementById('modal-mobile-columns{{ $_ms }}')?.close()">{!! mrcatz_icon('close') !!}</button>
            </div>
            <div class="px-5 py-4 space-y-1 max-h-[60vh] overflow-y-auto">
                @foreach(range(0, $totalCols - 1) as $ci)
                    <label class="flex items-center gap-3 px-3 py-3 rounded-lg hover:bg-base-200/50 cursor-pointer active:bg-base-200/70">
                        <input type="checkbox" class="checkbox checkbox-sm checkbox-primary"
                               @checked(!in_array($ci, $hiddenColumns))
                               wire:click="toggleColumn({{ $ci }})"/>
                        <span class="text-sm text-base-content/70">{{ $posts->getHead($ci) }}</span>
                    </label>
                @endforeach
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
@endif

{{-- Mobile preset bottom-sheet --}}
@if(count($filters) > 0 || $showSearch)
    <dialog id="modal-mobile-preset{{ $_ms }}" class="modal modal-bottom sm:hidden" aria-modal="true" aria-labelledby="modal-preset-title{{ $_ms }}">
        <div class="modal-box bg-base-100 rounded-t-2xl shadow-2xl max-w-lg p-0">
            <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-base-content/10">
                <h3 id="modal-preset-title{{ $_ms }}" class="text-sm font-bold text-base-content flex items-center gap-2">
                    {!! mrcatz_icon('bookmarks', 'text-primary') !!}
                    {{ mrcatz_lang('filter_preset') }}
                </h3>
                <form method="dialog">
                    <button class="btn btn-ghost btn-sm btn-circle hover:bg-base-200">{!! mrcatz_icon('close') !!}</button>
                </form>
            </div>
            <div class="px-5 py-4 space-y-2">
                @include('mrcatz::components.ui.partials.preset-content')
            </div>
        </div>
        <form method="dialog" class="modal-backdrop"><button>close</button></form>
    </dialog>
@endif

{{-- Loading overlay --}}
@if($withLoading)
    @if($load_start)
        <div style="position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
            <div class="flex flex-col items-center gap-3">
                <span class="loading loading-dots loading-xl text-primary"></span>
                <span class="text-white text-sm font-medium">{{ mrcatz_lang('loading') }}</span>
            </div>
        </div>
    @endif
    <div wire:loading.delay.flex wire:target="showLoading, searchData, goToP, nextPage, previousPage, change, paginate, saveData, dropData, resetData, orderData, editData, deleteData, exportData, bulkDelete"
         class="flex items-center justify-center"
         style="display:none;position:fixed;top:0;left:0;width:100vw;height:100vh;z-index:9999;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px);">
        <div class="flex flex-col items-center gap-3">
            <span class="loading loading-dots loading-xl text-primary"></span>
            <span class="text-white text-sm font-medium">{{ mrcatz_lang('processing') }}</span>
        </div>
    </div>
@endif
