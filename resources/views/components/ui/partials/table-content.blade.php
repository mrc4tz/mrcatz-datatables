{{-- Table Content: data table, empty state, skeleton, pagination --}}
@if($posts->hasData())
    {{-- Mobile card view --}}
    <div class="md:hidden space-y-3 pt-3">
        @for($i = 0; $i < $posts->countRow(); $i++)
            <div class="rounded-xl border border-base-content/8 bg-base-100 shadow-sm transition-all duration-150"
                 :class="focusedRow === {{ $i }} ? 'ring-2 ring-primary/30 border-primary/20' : ''"
                 @click="focusedRow = {{ $i }}; @if($enableRowClick) $wire.rowClicked(JSON.parse('{{ json_encode($posts->getRowRawData($i)) }}')); @endif"
                 data-row="{{ json_encode($posts->getRowRawData($i)) }}">

                {{-- Card header: first data column as title --}}
                @php
                    $firstDataCol = null;
                    $restCols = [];
                    $actionCols = [];
                    foreach ($visibleColOrderMobile as $ci) {
                        if ($posts->getIndex($ci) != null) continue;
                        if ($posts->getKey($ci) == null && !$posts->isEditable($ci)) {
                            $actionCols[] = $ci;
                        } elseif ($firstDataCol === null) {
                            $firstDataCol = $ci;
                        } else {
                            $restCols[] = $ci;
                        }
                    }
                @endphp

                <div class="px-4 pt-3 pb-2 flex items-start justify-between gap-2">
                    <div class="flex-1 min-w-0 overflow-hidden">
                        @if($firstDataCol !== null)
                            @if($posts->isEditable($firstDataCol) && $posts->isEditableRow($i, $posts->getKey($firstDataCol)))
                                @include('mrcatz::components.ui.partials.inline-edit', [
                                    'variant' => 'mobile-header',
                                    'cellId' => $i . '_' . $posts->getKey($firstDataCol),
                                    'ref' => 'mc_' . $i . '_' . $firstDataCol,
                                    'value' => strip_tags($posts->getData($i, $firstDataCol)),
                                    'display' => $posts->getData($i, $firstDataCol),
                                    'columnKey' => $posts->getKey($firstDataCol),
                                    'rowIndex' => $i,
                                    'rowDataJson' => "JSON.parse('" . json_encode($posts->getRowRawData($i)) . "')",
                                    'inputSize' => 'input-sm',
                                    'head' => $posts->getHead($firstDataCol),
                                    'uppercaseClass' => $posts->isUppercase($firstDataCol) ? 'uppercase' : '',
                                ])
                            @else
                                <span class="text-[10px] text-base-content/30 uppercase tracking-wider font-semibold">{{ $posts->getHead($firstDataCol) }}</span>
                                <p class="text-sm font-semibold text-base-content truncate @if($posts->isUppercase($firstDataCol)) uppercase @endif">{!! $posts->getData($i, $firstDataCol) !!}</p>
                            @endif
                        @endif
                    </div>
                    <div class="flex items-center gap-1 shrink-0">
                        @if($bulkShow && $posts->isBulkEnabled($i))
                            <input type="checkbox" class="checkbox checkbox-sm checkbox-primary" @click.stop
                                   aria-label="{{ mrcatz_lang('btn_select') }} row {{ $i + 1 }}"
                                   value="{{ $posts->getRowRawData($i)->{$bulkPrimaryKey} }}"
                                   wire:model.live="selectedRows"/>
                        @endif
                        @foreach($actionCols as $aci)
                            <span @click.stop>{!! $posts->getData($i, $aci) !!}</span>
                        @endforeach
                    </div>
                </div>

                {{-- Card body: remaining fields as pills --}}
                @if(count($restCols) > 0)
                    <div class="px-4 pb-3 grid grid-cols-2 gap-1.5">
                        @foreach($restCols as $ci)
                            @if($posts->isEditable($ci) && $posts->isEditableRow($i, $posts->getKey($ci)))
                                <div class="px-3 py-2 rounded-lg bg-base-200/40 min-w-0 overflow-hidden">
                                    @include('mrcatz::components.ui.partials.inline-edit', [
                                        'variant' => 'mobile-pill',
                                        'cellId' => $i . '_' . $posts->getKey($ci),
                                        'ref' => 'mc_' . $i . '_' . $ci,
                                        'value' => strip_tags($posts->getData($i, $ci)),
                                        'display' => $posts->getData($i, $ci),
                                        'columnKey' => $posts->getKey($ci),
                                        'rowIndex' => $i,
                                        'rowDataJson' => "JSON.parse('" . json_encode($posts->getRowRawData($i)) . "')",
                                        'inputSize' => 'input-xs',
                                        'head' => $posts->getHead($ci),
                                        'uppercaseClass' => $posts->isUppercase($ci) ? 'uppercase' : '',
                                    ])
                                </div>
                            @else
                                <div class="px-3 py-2 rounded-lg bg-base-200/40 min-w-0 overflow-hidden">
                                    <span class="text-[11px] text-base-content/40 block mb-0.5">{{ $posts->getHead($ci) }}</span>
                                    <span class="text-sm text-base-content/80 block truncate @if($posts->isUppercase($ci)) uppercase @endif">{!! $posts->getData($i, $ci) !!}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                {{-- Expand: open modal on mobile --}}
                @if($showExpandMobile && $posts->isExpandEnabled($i))
                    <div class="px-4 pb-3">
                        <button @click.stop="$dispatch('open-mobile-expand', { index: {{ $i }} })"
                                class="w-full flex items-center justify-center gap-1.5 py-1.5 rounded-lg bg-base-200/30 hover:bg-base-200/60 text-base-content/40 hover:text-base-content/60 transition-colors text-xs">
                            {!! mrcatz_icon('info', 'text-sm') !!}
                            {{ mrcatz_lang('btn_details') }}
                        </button>
                    </div>
                @endif
            </div>
        @endfor
    </div>

    {{-- Mobile expand modal --}}
    @if($showExpandMobile)
        <dialog id="modal-mobile-expand" class="modal modal-bottom md:hidden" aria-modal="true" aria-labelledby="modal-expand-title"
                x-data="{ expandIndex: -1 }"
                x-on:open-mobile-expand.window="expandIndex = $event.detail.index; $el.showModal()">
            <div class="modal-box bg-base-100 rounded-t-2xl shadow-2xl max-w-lg p-0">
                <div class="flex items-center justify-between px-5 pt-4 pb-3 border-b border-base-content/10">
                    <h3 id="modal-expand-title" class="text-sm font-bold text-base-content flex items-center gap-2">
                        {!! mrcatz_icon('info', 'text-primary') !!}
                        {{ mrcatz_lang('btn_details') }}
                    </h3>
                    <form method="dialog">
                        <button class="btn btn-ghost btn-sm btn-circle hover:bg-base-200">{!! mrcatz_icon('close') !!}</button>
                    </form>
                </div>
                <div class="px-5 py-4 max-h-[60vh] overflow-y-auto">
                    @for($i = 0; $i < $posts->countRow(); $i++)
                        <div x-show="expandIndex === {{ $i }}" x-cloak>
                            {!! $posts->getExpandContent($i) !!}
                        </div>
                    @endfor
                </div>
            </div>
            <form method="dialog" class="modal-backdrop"><button>close</button></form>
        </dialog>
    @endif

    {{-- Desktop table view --}}
    <div class="hidden md:block overflow-x-auto @if($stickyHeader) max-h-[70vh] overflow-y-auto @endif">
        <table class="table outline-none" role="grid" aria-label="{{ $tableTitle ?: $title ?: 'Data table' }}"
               @if($enableKeyboardNav)
               tabindex="0"
               @keydown.arrow-up.prevent="navUp(); $el.querySelectorAll('tbody tr')[focusedRow]?.scrollIntoView({block:'nearest'})"
               @keydown.arrow-down.prevent="navDown(); $el.querySelectorAll('tbody tr')[focusedRow]?.scrollIntoView({block:'nearest'})"
               @keydown.escape="if ($event.target.tagName === 'INPUT' || $event.target.tagName === 'TEXTAREA') return; $event.preventDefault(); focusedRow = -1"
               @keydown.enter="if ($event.target.tagName === 'INPUT' || $event.target.tagName === 'TEXTAREA') return; $event.preventDefault(); editFocused($el, $event)"
               @keydown.delete="if ($event.target.tagName === 'INPUT' || $event.target.tagName === 'TEXTAREA') return; $event.preventDefault(); deleteFocused($el)"
               @keydown.backspace="if ($event.target.tagName === 'INPUT' || $event.target.tagName === 'TEXTAREA') return; $event.preventDefault(); deleteFocused($el)"
               @endif>
            <thead>
            <tr class="bg-base-200/50 border-b border-base-content/10 @if($stickyHeader) sticky top-0 z-10 bg-base-200 @endif">
                @if($showExpandDesktop)
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

                @foreach($visibleColOrderDesktop as $pos => $ci)
                    <th class="text-xs font-semibold uppercase tracking-wider text-base-content/50 relative
                        @if($posts->gravity($ci)=='center') text-center
                        @elseif($posts->gravity($ci)=='right') text-right
                        @else text-left @endif"
                        :style="getColWidth({{ $ci }}) ? 'width:' + getColWidth({{ $ci }}) + ';min-width:' + getColWidth({{ $ci }}) : ''"
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
                                    x-data @click="$event.shiftKey ? $wire.addSort({{ json_encode($posts->getKey($ci)) }}, {{ json_encode($posts->getOrder($ci)) }}) : $wire.orderData({{ json_encode($posts->getKey($ci)) }}, {{ json_encode($posts->getOrder($ci)) }})">
                                {{ $posts->getHead($ci) }}
                                @if($posts->getOrder($ci) === 'asc')
                                    {!! mrcatz_icon('keyboard_arrow_up', 'text-sm text-primary/50') !!}
                                @elseif($posts->getOrder($ci) === 'desc')
                                    {!! mrcatz_icon('keyboard_arrow_down', 'text-sm text-primary/50') !!}
                                @else
                                    {!! mrcatz_icon('unfold_more', 'text-sm opacity-40') !!}
                                @endif
                                @if(!empty($multiSort))
                                    @foreach($multiSort as $si => $ms)
                                        @if($ms['key'] === $posts->getKey($ci))
                                            <span class="badge badge-xs badge-primary text-[9px] font-bold">{{ $si + 1 }}</span>
                                        @endif
                                    @endforeach
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
                            <div style="position:absolute;right:-2px;top:25%;bottom:25%;width:12px;cursor:col-resize;z:10;display:flex;align-items:center;justify-content:center;"
                                 @mousedown.prevent.stop="startResize($event, $el.parentElement, {{ $ci }})"
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
                    @click="focusedRow = {{ $i }}; @if($enableRowClick) $wire.rowClicked(JSON.parse($el.dataset.row)); @endif"
                    data-row="{{ json_encode($posts->getRowRawData($i)) }}">

                    @if($showExpandDesktop)
                        <td class="w-8 text-center">
                            @if($posts->isExpandEnabled($i))
                                <span class="inline-flex items-center justify-center cursor-pointer" style="transition: transform 500ms ease-in-out" @click.stop="toggleExpand({{ $i }})"
                                      :style="expandedRows.includes({{ $i }}) ? 'transform: rotate(90deg)' : 'transform: rotate(0deg)'">{!! mrcatz_icon('chevron_right', 'text-sm text-base-content/40') !!}</span>
                            @endif
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

                    @foreach($visibleColOrderDesktop as $ci)
                        @if($posts->isEditable($ci) && $posts->isEditableRow($i, $posts->getKey($ci)))
                            <td class="text-sm @if($posts->isUppercase($ci)) uppercase @endif
                                @if($posts->gravity($ci)=='center') text-center
                                @elseif($posts->gravity($ci)=='right') text-right
                                @else text-left @endif">
                                @include('mrcatz::components.ui.partials.inline-edit', [
                                    'variant' => 'desktop',
                                    'cellId' => $i . '_' . $posts->getKey($ci),
                                    'ref' => 'ie_' . $i . '_' . $ci,
                                    'value' => strip_tags($posts->getData($i, $ci)),
                                    'display' => $posts->getData($i, $ci),
                                    'columnKey' => $posts->getKey($ci),
                                    'rowIndex' => $i,
                                    'rowDataJson' => "JSON.parse(\$el.closest('tr').dataset.row)",
                                    'inputSize' => 'input-xs',
                                    'head' => $posts->getHead($ci),
                                    'uppercaseClass' => $posts->isUppercase($ci) ? 'uppercase' : '',
                                ])
                            </td>
                        @elseif($posts->isTH($ci))
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
                @if($showExpandDesktop && $posts->isExpandEnabled($i))
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
    @if($emptyStateView)
        @include($emptyStateView, ['search' => $search, 'activeFilterCount' => $activeFilterCount])
    @else
        <div class="flex flex-col items-center justify-center py-20 px-4">
            <div class="w-16 h-16 rounded-full bg-base-200 flex items-center justify-center mb-4">
                @if(!empty($search) || $activeFilterCount > 0)
                    {!! mrcatz_icon('search_off', 'text-3xl text-base-content/30') !!}
                @else
                    {!! mrcatz_icon('inbox', 'text-3xl text-base-content/30') !!}
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
@endif

{{-- Loading skeleton --}}
<div wire:loading wire:target="searchData, goToP, nextPage, previousPage, change, paginate, resetData, orderData">
    {{-- Mobile skeleton --}}
    <div class="md:hidden space-y-3 pt-3">
        @for($sk = 0; $sk < min($p ?? 5, 3); $sk++)
            <div class="rounded-xl border border-base-content/8 bg-base-100 p-4 space-y-2">
                <div class="space-y-1">
                    <div class="skeleton h-2.5 w-12 rounded"></div>
                    <div class="skeleton h-4 w-3/4 rounded"></div>
                </div>
                <div class="grid grid-cols-2 gap-1.5">
                    @for($sc = 0; $sc < min($totalCols - 1, 4); $sc++)
                        <div class="px-3 py-2 rounded-lg bg-base-200/40 space-y-1">
                            <div class="skeleton h-2.5 w-10 rounded"></div>
                            <div class="skeleton h-3.5 w-full rounded"></div>
                        </div>
                    @endfor
                </div>
            </div>
        @endfor
    </div>
    {{-- Desktop skeleton --}}
    <div class="hidden md:block overflow-x-auto">
        <table class="table">
            <tbody>
                @for($sk = 0; $sk < min($p ?? 5, 5); $sk++)
                    <tr class="border-b border-base-content/5">
                        @for($sc = 0; $sc < $totalCols; $sc++)
                            <td><div class="skeleton h-4 w-full rounded"></div></td>
                        @endfor
                    </tr>
                @endfor
            </tbody>
        </table>
    </div>
</div>

@if($usePagination)
    <div class="px-4 py-3 border-t border-base-content/5 md:border-t rounded-xl md:rounded-none bg-base-100 md:bg-transparent shadow-sm md:shadow-none mt-3 md:mt-0 @if($borderContainer) p-4 @endif">
        {{ $posts->links('mrcatz::components.ui.pagination') }}
    </div>
@endif
