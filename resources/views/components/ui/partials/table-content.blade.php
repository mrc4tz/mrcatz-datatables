{{-- Table Content: data table, empty state, skeleton, pagination --}}
@if($posts->hasData())
    {{-- Mobile card view --}}
    <div class="md:hidden divide-y divide-base-content/5">
        @for($i = 0; $i < $posts->countRow(); $i++)
            <div class="p-4 space-y-2 transition-colors duration-150"
                 :style="focusedRow === {{ $i }} ? 'background:color-mix(in srgb,var(--color-primary) 15%,transparent)' : ''"
                 @click="focusedRow = {{ $i }}; @if($enableRowClick) $wire.rowClicked(JSON.parse('{{ json_encode($posts->getRowRawData($i)) }}')); @endif"
                 data-row="{{ json_encode($posts->getRowRawData($i)) }}">

                @if($bulkShow)
                    <div class="flex items-center justify-end">
                        @if($posts->isBulkEnabled($i))
                            <input type="checkbox" class="checkbox checkbox-sm checkbox-primary"
                                   aria-label="{{ mrcatz_lang('btn_select') }} row {{ $i + 1 }}"
                                   value="{{ $posts->getRowRawData($i)->{$bulkPrimaryKey} }}"
                                   wire:model.live="selectedRows"/>
                        @endif
                    </div>
                @endif

                @foreach($visibleColOrder as $ci)
                    @if($posts->getIndex($ci) != null)
                        @continue
                    @endif
                    @if($posts->isEditable($ci))
                        <div class="flex flex-col gap-0.5"
                             x-data="{
                                 editing: false,
                                 val: '{{ e(strip_tags($posts->getData($i, $ci))) }}',
                                 error: '',
                                 submit() {
                                     this.editing = false;
                                     this.error = '';
                                     $wire.inlineUpdate(JSON.parse('{{ json_encode($posts->getRowRawData($i)) }}'), '{{ $posts->getKey($ci) }}', this.val);
                                 }
                             }"
                             x-on:inline-validation-error.window="if ($event.detail.columnKey === '{{ $posts->getKey($ci) }}') { error = $event.detail.error; editing = true; $nextTick(() => $refs['mc_{{ $i }}_{{ $ci }}']?.focus()) }">
                            <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">{{ $posts->getHead($ci) }}</span>
                            <span x-show="!editing" @click.stop="editing = true; error = ''; $nextTick(() => $refs['mc_{{ $i }}_{{ $ci }}']?.focus())"
                                  class="text-sm cursor-text border-b border-dashed border-base-content/20 py-1 @if($posts->isUppercase($ci)) uppercase @endif">{!! $posts->getData($i, $ci) !!}</span>
                            <div x-show="editing" class="flex flex-col" @click.stop>
                                <input x-ref="mc_{{ $i }}_{{ $ci }}" x-model="val"
                                       @keydown.enter.prevent="submit()"
                                       @keydown.escape.prevent="editing = false; error = ''"
                                       @blur="if (!error) { editing = false }"
                                       class="input input-sm input-bordered w-full text-sm"
                                       :class="error ? 'input-error' : ''"/>
                                <span x-show="error" x-text="error" class="text-error text-xs mt-0.5"></span>
                            </div>
                        </div>
                    @elseif($posts->getKey($ci) != null)
                        <div class="flex flex-col gap-0.5">
                            <span class="text-xs font-semibold text-base-content/40 uppercase tracking-wide">{{ $posts->getHead($ci) }}</span>
                            <span class="text-sm @if($posts->isUppercase($ci)) uppercase @endif">{!! $posts->getData($i, $ci) !!}</span>
                        </div>
                    @else
                        {{-- Custom column (actions etc.) --}}
                        <div class="flex items-center justify-end gap-2 pt-1">
                            {!! $posts->getData($i, $ci) !!}
                        </div>
                    @endif
                @endforeach

                @if($showExpand)
                    <div @click.stop="toggleExpand({{ $i }})" class="flex items-center gap-1 cursor-pointer text-base-content/40 pt-1">
                        <span class="transition-transform duration-200" :class="expandedRows.includes({{ $i }}) ? 'rotate-90' : ''">{!! mrcatz_icon('chevron_right', 'text-sm') !!}</span>
                        <span class="text-xs">{{ mrcatz_lang('btn_details') }}</span>
                    </div>
                    <div x-show="expandedRows.includes({{ $i }})" class="text-sm pt-2"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        {!! $posts->getExpandContent($i) !!}
                    </div>
                @endif
            </div>
        @endfor
    </div>

    {{-- Desktop table view --}}
    <div class="hidden md:block overflow-x-auto @if($stickyHeader) max-h-[70vh] overflow-y-auto @endif">
        <table class="table outline-none" role="grid" aria-label="{{ $tableTitle ?: $title ?: 'Data table' }}"
               @if($enableKeyboardNav)
               tabindex="0"
               @keydown.arrow-up.prevent="navUp(); $el.querySelectorAll('tbody tr')[focusedRow]?.scrollIntoView({block:'nearest'})"
               @keydown.arrow-down.prevent="navDown(); $el.querySelectorAll('tbody tr')[focusedRow]?.scrollIntoView({block:'nearest'})"
               @keydown.escape.prevent="focusedRow = -1"
               @keydown.enter.prevent="editFocused($el, $event)"
               @keydown.delete.prevent="deleteFocused($el)"
               @keydown.backspace.prevent="deleteFocused($el)"
               @endif>
            <thead>
            <tr class="bg-base-200/50 border-b border-base-content/10 @if($stickyHeader) sticky top-0 z-10 bg-base-200 @endif">
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

                @foreach($visibleColOrder as $pos => $ci)
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

                    @if($showExpand)
                        <td class="w-8 text-center" @click.stop="toggleExpand({{ $i }})">
                            <span class="transition-transform duration-200"
                                  :class="expandedRows.includes({{ $i }}) ? 'rotate-90' : ''">{!! mrcatz_icon('chevron_right', 'text-sm text-base-content/40') !!}</span>
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

                    @foreach($visibleColOrder as $ci)
                        @if($posts->isEditable($ci))
                            <td class="text-sm @if($posts->isUppercase($ci)) uppercase @endif
                                @if($posts->gravity($ci)=='center') text-center
                                @elseif($posts->gravity($ci)=='right') text-right
                                @else text-left @endif"
                                x-data="{
                                    editing: false,
                                    val: '{{ e(strip_tags($posts->getData($i, $ci))) }}',
                                    error: '',
                                    submit() {
                                        this.editing = false;
                                        this.error = '';
                                        $wire.inlineUpdate(JSON.parse($el.closest('tr').dataset.row), '{{ $posts->getKey($ci) }}', this.val);
                                    }
                                }"
                                x-on:inline-validation-error.window="if ($event.detail.columnKey === '{{ $posts->getKey($ci) }}') { error = $event.detail.error; editing = true; $nextTick(() => $refs['ie_{{ $i }}_{{ $ci }}']?.focus()) }"
                                @dblclick.stop="editing = true; error = ''; $nextTick(() => $refs['ie_{{ $i }}_{{ $ci }}']?.focus())"
                                @click.stop="if (window.innerWidth < 768 && !editing) { editing = true; error = ''; $nextTick(() => $refs['ie_{{ $i }}_{{ $ci }}']?.focus()) }">
                                <span x-show="!editing" class="cursor-text border-b border-dashed border-base-content/20">{!! $posts->getData($i, $ci) !!}</span>
                                <div x-show="editing" class="inline-flex flex-col" @click.stop>
                                    <input x-ref="ie_{{ $i }}_{{ $ci }}"
                                           x-model="val"
                                           @keydown.enter.prevent="submit()"
                                           @keydown.escape.prevent="editing = false; error = ''"
                                           @blur="if (!error) { editing = false }"
                                           class="input input-xs input-bordered w-full max-w-[200px] text-sm"
                                           :class="error ? 'input-error' : ''"/>
                                    <span x-show="error" x-text="error" class="text-error text-xs mt-0.5 max-w-[200px]"></span>
                                </div>
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
    <div class="md:hidden divide-y divide-base-content/5">
        @for($sk = 0; $sk < min($p ?? 5, 3); $sk++)
            <div class="p-4 space-y-3">
                @for($sc = 0; $sc < min($totalCols, 4); $sc++)
                    <div class="space-y-1">
                        <div class="skeleton h-3 w-16 rounded"></div>
                        <div class="skeleton h-4 w-full rounded"></div>
                    </div>
                @endfor
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
    <div class="px-4 py-3 border-t border-base-content/5 @if($borderContainer) p-4 @endif">
        {{ $posts->links('mrcatz::components.ui.pagination') }}
    </div>
@endif
