{{-- Table Content: data table, empty state, skeleton, pagination --}}
<div class="relative">
@if($posts->hasData())
    {{-- Mobile card view --}}
    <div class="md:hidden space-y-3 pt-3" wire:loading.class="invisible" wire:target="searchData, goToP, nextPage, previousPage, change, paginate, resetData, orderData">
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
                    $imageCol = null;
                    // Find image column from ALL visible columns (not just mobile-filtered)
                    foreach ($visibleColOrder as $ci) {
                        if (($posts->getColumnType($ci) ?? null) === 'image') {
                            $imageCol = $ci;
                            break;
                        }
                    }
                    foreach ($visibleColOrderMobile as $ci) {
                        if ($posts->getIndex($ci) != null) continue;
                        if ($imageCol !== null && $ci === $imageCol) continue;
                        if (($posts->getColumnType($ci) ?? null) === 'action') {
                            $actionCols[] = $ci;
                        } elseif ($firstDataCol === null) {
                            $firstDataCol = $ci;
                        } else {
                            $restCols[] = $ci;
                        }
                    }
                @endphp

                <div class="px-4 pt-3 pb-2 flex items-start justify-between gap-3">
                    {{-- Avatar from image column --}}
                    @if($imageCol !== null)
                        <div class="shrink-0 mt-0.5">
                            {!! $posts->getData($i, $imageCol) !!}
                        </div>
                    @endif
                    <div class="flex-1 min-w-0 overflow-hidden">
                        @if($firstDataCol !== null)
                            @if($posts->isEditable($firstDataCol) && $posts->isEditableRow($i, $posts->getKey($firstDataCol)))
                                <div x-data="{
                                         editing: false,
                                         saving: false,
                                         val: '{{ e(strip_tags($posts->getData($i, $firstDataCol))) }}',
                                         error: '',
                                         submit() {
                                             this.editing = false;
                                             this.error = '';
                                             this.saving = true;
                                             $wire.inlineUpdate(JSON.parse('{{ json_encode($posts->getRowRawData($i)) }}'), '{{ $posts->getKey($firstDataCol) }}', this.val, {{ $i }});
                                         }
                                     }"
                                     x-on:inline-validation-error.window="if ($event.detail.cellId === '{{ $i }}_{{ $posts->getKey($firstDataCol) }}') { saving = false; error = $event.detail.error; editing = true; $nextTick(() => $refs['mc_{{ $i }}_{{ $firstDataCol }}']?.focus()) }"
                                     x-on:inline-save-done.window="if ($event.detail.cellId === '{{ $i }}_{{ $posts->getKey($firstDataCol) }}') { saving = false }">
                                    <span class="text-[10px] text-base-content/30 uppercase tracking-wider font-semibold flex items-center gap-1">{{ $posts->getHead($firstDataCol) }} <svg class="w-2.5 h-2.5 text-primary/40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg></span>
                                    <p x-show="!editing && !saving" @click.stop="editing = true; error = ''; $nextTick(() => $refs['mc_{{ $i }}_{{ $firstDataCol }}']?.focus())"
                                       class="text-sm font-semibold text-base-content truncate cursor-text rounded bg-primary/5 border border-dashed border-primary/20 px-1.5 py-0.5 @if($posts->isUppercase($firstDataCol)) uppercase @endif">{!! $posts->getData($i, $firstDataCol) !!}</p>
                                    <span x-show="saving" x-cloak class="inline-flex items-center gap-1.5 py-0.5 text-base-content/40">
                                        <span class="loading loading-spinner loading-xs"></span>
                                    </span>
                                    <div x-show="editing" class="flex flex-col mt-0.5" @click.stop>
                                        <input x-ref="mc_{{ $i }}_{{ $firstDataCol }}" x-model="val"
                                               @keydown.enter.prevent="submit()"
                                               @keydown.escape.prevent="editing = false; error = ''"
                                               @blur="if (!error) { editing = false }"
                                               class="input input-sm input-bordered w-full text-sm font-semibold"
                                               :class="error ? 'input-error' : ''"/>
                                        <span x-show="error" x-text="error" role="alert" aria-live="assertive" class="text-error text-xs mt-0.5"></span>
                                    </div>
                                </div>
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
                                <div class="px-3 py-2 rounded-lg bg-base-200/40 min-w-0 overflow-hidden"
                                     x-data="{
                                         editing: false,
                                         saving: false,
                                         val: '{{ e(strip_tags($posts->getData($i, $ci))) }}',
                                         error: '',
                                         submit() {
                                             this.editing = false;
                                             this.error = '';
                                             this.saving = true;
                                             $wire.inlineUpdate(JSON.parse('{{ json_encode($posts->getRowRawData($i)) }}'), '{{ $posts->getKey($ci) }}', this.val, {{ $i }});
                                         }
                                     }"
                                     x-on:inline-validation-error.window="if ($event.detail.cellId === '{{ $i }}_{{ $posts->getKey($ci) }}') { saving = false; error = $event.detail.error; editing = true; $nextTick(() => $refs['mc_{{ $i }}_{{ $ci }}']?.focus()) }"
                                     x-on:inline-save-done.window="if ($event.detail.cellId === '{{ $i }}_{{ $posts->getKey($ci) }}') { saving = false }">
                                    <span class="text-[11px] text-base-content/40 block mb-0.5 flex items-center gap-1">{{ $posts->getHead($ci) }} <svg class="w-2.5 h-2.5 text-primary/40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg></span>
                                    <span x-show="!editing && !saving" @click.stop="editing = true; error = ''; $nextTick(() => $refs['mc_{{ $i }}_{{ $ci }}']?.focus())"
                                          class="text-sm text-base-content/80 cursor-text block truncate rounded bg-primary/5 border border-dashed border-primary/20 px-1.5 py-0.5 @if($posts->isUppercase($ci)) uppercase @endif">{!! $posts->getData($i, $ci) !!}</span>
                                    <span x-show="saving" x-cloak class="inline-flex items-center gap-1.5 py-0.5 text-base-content/40">
                                        <span class="loading loading-spinner loading-xs"></span>
                                    </span>
                                    <div x-show="editing" class="flex flex-col mt-0.5" @click.stop>
                                        <input x-ref="mc_{{ $i }}_{{ $ci }}" x-model="val"
                                               @keydown.enter.prevent="submit()"
                                               @keydown.escape.prevent="editing = false; error = ''"
                                               @blur="if (!error) { editing = false }"
                                               class="input input-xs input-bordered w-full text-sm"
                                               :class="error ? 'input-error' : ''"/>
                                        <span x-show="error" x-text="error" role="alert" aria-live="assertive" class="text-error text-xs mt-0.5"></span>
                                    </div>
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

    {{-- Desktop table view.
         When $stickyHeader is on, we put the scroll container into its own
         stacking context via `isolate` (isolation: isolate). That way the
         z-10 on the sticky <th> is scoped INSIDE this container — it beats
         hover/focus rings on action-column buttons inside <tbody>, but it
         doesn't leak out to override the toolbar tooltips that live above
         the container. Without isolate, toolbar tooltips (z:1) get clipped
         under the sticky header; without z-10 inside, action buttons paint
         on top of the sticky header. Isolate + z-10 solves both. --}}
    <div class="hidden md:block overflow-x-auto @if($stickyHeader) max-h-[70vh] overflow-y-auto isolate @endif" wire:loading.class="invisible" wire:target="searchData, goToP, nextPage, previousPage, change, paginate, resetData, orderData">
        <table class="table outline-none" role="grid" aria-label="{{ $tableTitle ?: $title ?: 'Data table' }}"
               @if($enableKeyboardNav)
               tabindex="0"
               @keydown.arrow-up.prevent="navUp(); $el.querySelectorAll('tbody tr')[focusedRow]?.scrollIntoView({block:'nearest'})"
               @keydown.arrow-down.prevent="navDown(); $el.querySelectorAll('tbody tr')[focusedRow]?.scrollIntoView({block:'nearest'})"
               @keydown.escape="if ($event.target.tagName === 'INPUT' || $event.target.tagName === 'TEXTAREA') return; $event.preventDefault(); focusedRow = -1"
               @if($posts->hasEditAction)
               @keydown.enter="if ($event.target.tagName === 'INPUT' || $event.target.tagName === 'TEXTAREA') return; $event.preventDefault(); editFocused($el, $event)"
               @endif
               @if($posts->hasDeleteAction)
               @keydown.delete="if ($event.target.tagName === 'INPUT' || $event.target.tagName === 'TEXTAREA') return; $event.preventDefault(); deleteFocused($el)"
               @keydown.backspace="if ($event.target.tagName === 'INPUT' || $event.target.tagName === 'TEXTAREA') return; $event.preventDefault(); deleteFocused($el)"
               @endif
               @endif>
            {{-- Sticky header background lives on each <th>, not on <tr>.
                 Browsers don't reliably propagate the <tr> background to its
                 <th> children when position:sticky is in play, so the underlying
                 rows would bleed through. Putting bg-base-200 on every <th>
                 directly (solid, not /50 opacity) keeps the header row opaque.

                 z-10 is needed to beat action-column button hover/focus rings
                 and other positioned body content inside <tbody>. The outer
                 scroll wrapper has `isolate` when sticky is enabled, so this
                 z-10 is SCOPED to the table's stacking context — it won't
                 leak out to override toolbar tooltips that live above the
                 table. See the wrapper div above for the isolate note. --}}
            @php
                $thStickyBg = $stickyHeader
                    ? 'bg-base-200 sticky top-0 z-10'
                    : 'bg-base-200/50';
            @endphp
            <thead>
            <tr class="border-b border-base-content/10">
                @if($showExpandDesktop)
                    <th class="w-8 {{ $thStickyBg }}"></th>
                @endif
                @if($bulkShow)
                    <th class="w-10 text-center {{ $thStickyBg }}">
                        <input type="checkbox" class="checkbox checkbox-sm checkbox-primary"
                               aria-label="{{ mrcatz_lang('btn_select') }} all"
                               @checked($selectAll)
                               wire:click="toggleSelectAll"/>
                    </th>
                @endif

                @foreach($visibleColOrderDesktop as $pos => $ci)
                    <th class="{{ $thStickyBg }} text-xs font-semibold uppercase tracking-wider text-base-content/50 relative
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
                                {{-- Explicit w-6 h-6 on the wrapper guarantees
                                     a 24×24 hit area / visible footprint even
                                     when host CSS resets svg sizing (e.g. the
                                     common `img, svg { max-width:100%;
                                     height:auto }` reset). Without it the
                                     inline-flex span collapses to 0 and the
                                     chevron disappears. --}}
                                <span class="inline-flex items-center justify-center cursor-pointer w-6 h-8" style="transition: transform 500ms ease-in-out" @click.stop="toggleExpand({{ $i }})"
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
                                    'cellId' => $i . '_' . $posts->getKey($ci),
                                    'ref' => 'ie_' . $i . '_' . $ci,
                                    'value' => strip_tags($posts->getData($i, $ci)),
                                    'display' => $posts->getData($i, $ci),
                                    'columnKey' => $posts->getKey($ci),
                                    'rowIndex' => $i,
                                    'rowDataJson' => "JSON.parse(\$el.closest('tr').dataset.row)",
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
                    {{-- Use Tailwind's `hidden` class toggle via :class instead of
                         Alpine's x-show. Alpine's x-show on <tr> relies on browser
                         default display (table-row), and some setups / browser
                         caches / Livewire morph cycles leave the element with
                         display:none stuck — the expand panel then never opens
                         even when expandedRows.includes(i) is true. Tailwind's
                         `hidden` utility sets display:none !important, and toggling
                         it off reliably restores table-row layout on every tr.
                         The inner <div> keeps x-show + transition for the fade. --}}
                    <tr :class="{ 'hidden': !expandedRows.includes({{ $i }}) }" class="bg-base-200/20">
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
    <div wire:loading.class="invisible" wire:target="searchData, goToP, nextPage, previousPage, change, paginate, resetData, orderData">
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
    </div>
@endif

{{-- Loading skeleton (absolute overlay to prevent layout shift) --}}
<div wire:loading wire:target="searchData, goToP, nextPage, previousPage, change, paginate, resetData, orderData"
     class="absolute inset-0 z-10 bg-base-100/80">
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
            <thead>
                <tr class="bg-base-200/50 border-b border-base-content/10">
                    @foreach($visibleColOrderDesktop as $ci)
                        <th class="text-xs font-semibold uppercase tracking-wider text-base-content/50">
                            <div class="skeleton h-3 w-16 rounded"></div>
                        </th>
                    @endforeach
                </tr>
            </thead>
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
</div>{{-- end relative wrapper --}}

@if(!$usePagination && $hasMoreRows && ($showLoadMoreButton ?? true))
    <div class="flex flex-col items-center gap-2 py-4">
        <p class="text-xs text-base-content/40">{{ mrcatz_lang('showing_rows', [':count' => $posts->countRow()]) }}</p>
        <button wire:click="loadMore" wire:loading.attr="disabled"
                class="btn btn-sm btn-outline btn-primary gap-1.5">
            <span wire:loading.remove wire:target="loadMore">{{ mrcatz_lang('btn_load_more') }}</span>
            <span wire:loading wire:target="loadMore" class="loading loading-spinner loading-xs"></span>
        </button>
    </div>
@endif

@if($usePagination && $posts->hasData())
    <div class="px-4 py-3 border-t border-base-content/5 md:border-t rounded-xl md:rounded-none bg-base-100 md:bg-transparent shadow-sm md:shadow-none mt-3 md:mt-0 @if($borderContainer) p-4 @endif" wire:loading.class="invisible" wire:target="searchData, goToP, nextPage, previousPage, change, paginate, resetData, orderData">
        {{ $posts->links('mrcatz::components.ui.pagination') }}
    </div>
@endif
