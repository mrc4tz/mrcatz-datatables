{{-- Keyboard hints --}}
@if($showKeyboardNavNote && $enableKeyboardNav && $posts->hasData())
    <div class="mt-2 flex items-center justify-center gap-3 flex-wrap" style="font-size:10px;color:oklch(var(--bc)/.15);">
        <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">&#8593;&#8595;</kbd> {{ mrcatz_lang('key_navigate') }}</span>
        <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">Enter</kbd> {{ mrcatz_lang('key_edit') }}</span>
        <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">Del/&#9003;</kbd> {{ mrcatz_lang('key_delete') }}</span>
        <span style="display:inline-flex;align-items:center;gap:3px;"><kbd class="kbd kbd-xs" style="color:oklch(var(--bc)/.18);font-weight:600;">Esc</kbd> {{ mrcatz_lang('key_cancel') }}</span>
    </div>
@endif

{{-- Export modal --}}
@if($showExportButton)
    <dialog id="modal-export" class="modal modal-bottom sm:modal-middle" wire:ignore.self x-data="{ format: 'excel', scope: 'filtered' }" aria-modal="true" aria-labelledby="modal-export-title">
        <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-lg" x-trap.noscroll="document.getElementById('modal-export')?.open">
            <div class="flex items-center justify-between pb-4 mb-5 border-b border-base-content/10">
                <h3 id="modal-export-title" class="text-lg font-bold text-base-content flex items-center gap-2">
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
                    <div class="grid grid-cols-2 gap-2">
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="format === 'excel' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                               @click="format = 'excel'">
                            {!! mrcatz_icon('table_view', 'text-2xl text-success') !!}
                            <div><p class="text-sm font-semibold text-base-content">Excel</p><p class="text-xs text-base-content/40">.xlsx</p></div>
                        </label>
                        <label class="flex items-center gap-3 p-3 rounded-xl border cursor-pointer transition-all"
                               :class="format === 'pdf' ? 'border-primary bg-primary/5' : 'border-base-content/10 hover:bg-base-200/50'"
                               @click="format = 'pdf'">
                            {!! mrcatz_icon('picture_as_pdf', 'text-2xl text-error') !!}
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
                    <div class="rounded-xl bg-base-200/40 p-4 space-y-3">
                        <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide flex items-center gap-1">
                            {!! mrcatz_icon('tune', 'text-xs') !!}
                            {{ mrcatz_lang('export_settings') }}
                        </p>
                        <div>
                            <label class="text-xs text-base-content/50 mb-1 block">{{ mrcatz_lang('export_search') }}</label>
                            <label class="input input-bordered input-sm flex items-center gap-2 w-full focus-within:input-primary transition-all">
                                {!! mrcatz_icon('search', 'text-sm text-base-content/30') !!}
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
                {!! mrcatz_icon('info', 'text-sm text-base-content/40') !!}
                <p class="text-sm text-base-content/60">
                    <span class="font-semibold text-base-content" wire:loading.remove wire:target="updateExportCount">{{ number_format($exportCount) }}</span>
                    <span class="loading loading-spinner loading-xs" wire:loading wire:target="updateExportCount"></span>
                    {{ mrcatz_lang('export_count') }}
                </p>
            </div>

            <div class="modal-action pt-4 mt-4 border-t border-base-content/10">
                <button class="btn btn-primary gap-2 px-6 shadow-sm"
                        x-on:click="$wire.exportData(format, scope); document.getElementById('modal-export').close();">
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
@endif

{{-- Reset confirmation modal --}}
<dialog id="modal-reset-confirm" class="modal modal-bottom sm:modal-middle" aria-modal="true" aria-labelledby="modal-reset-title">
    <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-sm text-center" x-data x-trap.noscroll="document.getElementById('modal-reset-confirm')?.open">
        <div class="w-14 h-14 rounded-full bg-warning/10 flex items-center justify-center mx-auto mb-4">
            {!! mrcatz_icon('restart_alt', 'text-2xl text-warning') !!}
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

{{-- Bulk delete modal --}}
@if($bulkPrimaryKey !== null)
    <dialog id="modal-bulk-delete" class="modal modal-bottom sm:modal-middle" wire:ignore.self aria-modal="true" aria-labelledby="modal-bulk-delete-title">
        <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl max-w-sm text-center" x-data x-trap.noscroll="document.getElementById('modal-bulk-delete')?.open">
            <div class="w-14 h-14 rounded-full bg-error/10 flex items-center justify-center mx-auto mb-4">
                {!! mrcatz_icon('delete_sweep', 'text-2xl text-error') !!}
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

{{-- Mobile columns bottom-sheet --}}
@if($enableColumnVisibility)
    <dialog id="modal-mobile-columns" class="modal modal-bottom sm:hidden" aria-modal="true" aria-labelledby="modal-columns-title">
        <div class="modal-box bg-base-100 rounded-t-2xl shadow-2xl max-w-lg p-0">
            <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-base-content/10">
                <h3 id="modal-columns-title" class="text-sm font-bold text-base-content flex items-center gap-2">
                    {!! mrcatz_icon('view_column', 'text-primary') !!}
                    {{ mrcatz_lang('col_visibility') }}
                </h3>
                <button class="btn btn-ghost btn-sm btn-circle hover:bg-base-200"
                        onclick="document.getElementById('modal-mobile-columns')?.close()">{!! mrcatz_icon('close') !!}</button>
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
    <dialog id="modal-mobile-preset" class="modal modal-bottom sm:hidden" aria-modal="true" aria-labelledby="modal-preset-title">
        <div class="modal-box bg-base-100 rounded-t-2xl shadow-2xl max-w-lg p-0">
            <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-base-content/10">
                <h3 id="modal-preset-title" class="text-sm font-bold text-base-content flex items-center gap-2">
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
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
            <div class="flex flex-col items-center gap-3">
                <span class="loading loading-spinner loading-lg text-primary"></span>
                <span class="text-white text-sm font-medium">{{ mrcatz_lang('loading') }}</span>
            </div>
        </div>
    @endif
    <div wire:loading wire:target="showLoading, searchData, goToP, nextPage, previousPage, change, paginate, saveData, dropData, resetData, orderData, editData, deleteData, exportData, bulkDelete"
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="flex flex-col items-center gap-3">
            <span class="loading loading-spinner loading-lg text-primary"></span>
            <span class="text-white text-sm font-medium">{{ mrcatz_lang('processing') }}</span>
        </div>
    </div>
@endif
