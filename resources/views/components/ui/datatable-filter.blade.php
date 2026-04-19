@php
    // Map MrCatz date format → HTML <input type="..."> value
    $dateInputType = function (string $format): string {
        return match ($format) {
            'datetime'   => 'datetime-local',
            'time'       => 'time',
            'time_hm'    => 'time',
            'month_year' => 'month',
            'year'       => 'number',
            default      => 'date',
        };
    };
@endphp
@if(count($dataFilters) > 0)
    @php $activeFilterValues = collect($activeFilters ?? [])->pluck('value', 'id')->toArray(); @endphp
    <div x-cloak x-show="open" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="p-4 mb-4 rounded-xl bg-base-200/50 border border-base-content/10" id="dtFilter">
        <div class="flex flex-wrap gap-4">
            @foreach($dataFilters as $f => $filter)
                @php $type = $filter['type'] ?? 'select'; @endphp

                @if($type === 'date')
                    @php
                        $htmlType = $dateInputType($filter['format'] ?? 'date');
                        $current  = $activeFilterValues[$filter['id']] ?? '';
                    @endphp
                    <div class="w-full sm:w-auto sm:min-w-48" wire:show="filterShow[{{$f}}]">
                        <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>
                        <input type="{{ $htmlType }}"
                               class="input input-bordered input-sm w-full text-sm focus:input-primary transition-all duration-200"
                               id="{{$filter['id']."_".$prefix}}"
                               @if($filter['min_date'] ?? null) min="{{ $filter['min_date'] }}" @endif
                               @if($filter['max_date'] ?? null) max="{{ $filter['max_date'] }}" @endif
                               value="{{ $current }}"
                               x-data
                               @change.debounce.{{ $filterDebounceDelay }}="$wire.change('{{$filter['id']}}', $event.target.value)">
                    </div>

                @elseif($type === 'date_range')
                    @php
                        $htmlType  = $dateInputType($filter['format'] ?? 'date');
                        $rangeVal  = $activeFilterValues[$filter['id']] ?? [];
                        $rangeFrom = is_array($rangeVal) ? ($rangeVal['from'] ?? '') : '';
                        $rangeTo   = is_array($rangeVal) ? ($rangeVal['to']   ?? '') : '';
                        $minAttr   = $filter['min_date'] ?? '';
                        $maxAttr   = $filter['max_date'] ?? '';
                        $popoverId = $filter['id'] . '_pop_' . $prefix;
                    @endphp
                    <div class="w-full sm:w-auto sm:min-w-64" wire:show="filterShow[{{$f}}]" wire:key="dr-wrap-{{ $filter['id'] }}">
                        <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>

                        {{-- wire:key hashes value bounds (from/to) + picker bounds
                             (min/max) so Alpine re-inits on BOTH a value change
                             (user X click, global reset, external resetFilter, etc.)
                             AND a bounds change (setFilterDateBounds at runtime).
                             Without min/max in the hash, a bounds override with no
                             value change would leave Alpine with the old min/max
                             baked into its x-data config. --}}
                        <div class="relative"
                             wire:key="dr-{{ $filter['id'] }}-{{ md5(
                                $rangeFrom . '|' . $rangeTo . '|' . ($minAttr ?? '') . '|' . ($maxAttr ?? '')
                             ) }}"
                             x-data="mrcatzDateRange({
                                from: @js($rangeFrom),
                                to: @js($rangeTo),
                                min: @js($minAttr),
                                max: @js($maxAttr),
                                filterId: @js($filter['id']),
                                labels: @js([
                                    'pick'      => mrcatz_lang('filter_date_pick'),
                                    'from'      => mrcatz_lang('filter_date_from'),
                                    'to'        => mrcatz_lang('filter_date_to'),
                                    'apply'     => mrcatz_lang('filter_date_apply'),
                                    'clear'     => mrcatz_lang('filter_date_clear'),
                                    'today'     => mrcatz_lang('filter_date_today'),
                                    'yesterday' => mrcatz_lang('filter_date_yesterday'),
                                    'last_7'    => mrcatz_lang('filter_date_last_7'),
                                    'last_30'   => mrcatz_lang('filter_date_last_30'),
                                    'this_month'=> mrcatz_lang('filter_date_this_month'),
                                    'last_6m'   => mrcatz_lang('filter_date_last_6m'),
                                    'this_year' => mrcatz_lang('filter_date_this_year'),
                                    'last_year' => mrcatz_lang('filter_date_last_year'),
                                ]),
                             })"
                             @keydown.escape.window="drOpen = false"
                             @click.outside="if (! ($refs.popover && $refs.popover.contains($event.target))) drOpen = false">

                            {{-- Clickable trigger.
                                 NOTE: must NOT contain a real <button> child — nested
                                 <button> is invalid HTML and browsers will close the
                                 outer button early, dropping subsequent children to a
                                 new line. The clear "x" is therefore a span with
                                 role=button for screen readers + click.stop. --}}
                            <button type="button"
                                    x-ref="trigger"
                                    @click="togglePopover($event.currentTarget)"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-sm rounded-lg border border-base-content/20 bg-base-100 hover:border-primary focus:border-primary focus:outline-none transition-colors"
                                    :class="{ 'border-primary': drOpen }">
                                <span class="flex items-center gap-2 min-w-0 flex-1">
                                    {!! mrcatz_icon('event', 'text-base-content/50 shrink-0 w-4 h-4') !!}
                                    <span class="truncate text-left" x-text="triggerText()"></span>
                                </span>
                                <span class="flex items-center gap-1 shrink-0">
                                    <span x-show="hasValue()"
                                          role="button"
                                          tabindex="0"
                                          @click.stop="clear()"
                                          @keydown.enter.stop.prevent="clear()"
                                          @keydown.space.stop.prevent="clear()"
                                          class="hover:bg-base-200 rounded-full p-0.5 transition cursor-pointer inline-flex items-center justify-center"
                                          :title="labels.clear">
                                        {!! mrcatz_icon('close', 'text-base-content/40 w-3.5 h-3.5') !!}
                                    </span>
                                    {!! mrcatz_icon('expand_more', 'text-base-content/40 w-4 h-4') !!}
                                </span>
                            </button>

                            {{-- Popover — teleported to <body> so it escapes any
                                 `overflow: hidden` ancestor (card wrappers, scroll
                                 containers, etc.) and uses position: fixed with
                                 coords computed from the trigger rect on open +
                                 scroll + resize.

                                 SSR-hidden via Tailwind `hidden` class (applies
                                 `display: none !important` pre-Alpine). Alpine
                                 then toggles it reactively via :class based on
                                 `open`. Tailwind's !important wins over any
                                 timing race during lazy-hydration / morph, so
                                 the popover can never leak visible at 0,0. --}}
                            <template x-teleport="body">
                            <div x-show="drOpen"
                                 x-ref="popover"
                                 style="display: none;"
                                 :style="{ top: popoverTop + 'px', left: popoverLeft + 'px' }"
                                 class="fixed z-[100] w-[22rem] bg-base-100 rounded-xl shadow-2xl border border-base-300 overflow-hidden">

                                <div class="grid grid-cols-[7.5rem_1fr]">
                                    {{-- Shortcuts column --}}
                                    <div class="bg-base-200/50 border-r border-base-300 py-2 max-h-[20rem] overflow-y-auto">
                                        <template x-for="preset in presets" :key="preset.key">
                                            <button type="button"
                                                    @click="applyPreset(preset.key)"
                                                    class="block w-full text-left px-3 py-1.5 text-xs hover:bg-base-300/50 transition-colors"
                                                    :class="activePreset === preset.key ? 'bg-primary/10 text-primary font-semibold' : 'text-base-content/70'"
                                                    x-text="preset.label"></button>
                                        </template>
                                    </div>

                                    {{-- Date inputs column --}}
                                    <div class="p-3 space-y-3">
                                        <div>
                                            <label class="block text-xs font-medium text-base-content/60 mb-1" x-text="labels.from"></label>
                                            <input type="{{ $htmlType }}"
                                                   x-ref="fromInput"
                                                   x-model="draftFrom"
                                                   @change="activePreset = null"
                                                   @if($minAttr) min="{{ $minAttr }}" @endif
                                                   :max="draftTo || @js($maxAttr)"
                                                   class="input input-bordered input-sm w-full text-sm">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-base-content/60 mb-1" x-text="labels.to"></label>
                                            <input type="{{ $htmlType }}"
                                                   x-ref="toInput"
                                                   x-model="draftTo"
                                                   @change="activePreset = null"
                                                   :min="draftFrom || @js($minAttr)"
                                                   @if($maxAttr) max="{{ $maxAttr }}" @endif
                                                   class="input input-bordered input-sm w-full text-sm">
                                        </div>

                                        <div class="flex gap-2 pt-1">
                                            <button type="button"
                                                    @click="clear(); drOpen = false"
                                                    class="btn btn-ghost btn-sm flex-1"
                                                    x-text="labels.clear"></button>
                                            <button type="button"
                                                    @click="apply()"
                                                    class="btn btn-primary btn-sm flex-1"
                                                    x-text="labels.apply"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            </template>
                        </div>
                    </div>

                @elseif($type === 'check')
                    @php
                        $ckVal  = $activeFilterValues[$filter['id']] ?? [];
                        $ckVal  = is_array($ckVal) ? array_values($ckVal) : [];
                        $ckMode = 'include';
                        foreach ($activeFilters ?? [] as $af) {
                            if (($af['id'] ?? null) === $filter['id']) {
                                $ckMode = !empty($af['exclude_mode']) ? 'exclude' : 'include';
                                break;
                            }
                        }
                    @endphp
                    <div class="w-full sm:w-auto sm:min-w-64" wire:show="filterShow[{{$f}}]" wire:key="ck-wrap-{{ $filter['id'] }}">
                        <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>

                        {{-- wire:key hashes:
                               • APPLIED value + mode → re-init after Apply / Reset / Clear-x
                               • filterData (option list)   → re-init after setFilterData()
                               • value / option column names → re-init after setFilterData()
                                 overrides the column mapping
                             Draft edits (check/uncheck, mode toggle, select-all,
                             clear-selection) never touch these, so the popover stays
                             open during normal editing — only filter-committing or
                             definition-mutating actions force an Alpine re-init, and
                             those close the popover anyway. --}}
                        <div class="relative"
                             wire:key="ck-{{ $filter['id'] }}-{{ md5(
                                json_encode($ckVal)
                                . '|' . $ckMode
                                . '|' . json_encode($filterData[$f] ?? [])
                                . '|' . ($filter['value'] ?? '')
                                . '|' . ($filter['option'] ?? '')
                                . '|' . ($filter['allow_exclude'] ? '1' : '0')
                             ) }}"
                             x-data="mrcatzCheckFilter({
                                values: @js($ckVal),
                                mode: @js($ckMode),
                                options: @js($filterData[$f] ?? []),
                                valueKey: @js($filter['value']),
                                optionKey: @js($filter['option']),
                                filterId: @js($filter['id']),
                                allowExclude: @js((bool) ($filter['allow_exclude'] ?? false)),
                                searchThreshold: @js($filter['search_threshold'] ?? null),
                                labels: @js([
                                    'pick'        => mrcatz_lang('filter_check_pick'),
                                    'search'      => mrcatz_lang('filter_check_search'),
                                    'noMatch'     => mrcatz_lang('filter_check_no_match'),
                                    'selected'    => mrcatz_lang('filter_check_selected'),
                                    'selectAll'   => mrcatz_lang('filter_check_select_all'),
                                    'clear'       => mrcatz_lang('filter_check_clear'),
                                    'apply'       => mrcatz_lang('filter_check_apply'),
                                    'reset'       => mrcatz_lang('filter_check_reset'),
                                    'include'     => mrcatz_lang('filter_check_mode_include'),
                                    'exclude'     => mrcatz_lang('filter_check_mode_exclude'),
                                    'notPrefix'   => mrcatz_lang('filter_check_not_prefix'),
                                    'plusMore'    => mrcatz_lang('filter_check_plus_more'),
                                ]),
                             })"
                             @keydown.escape.window="ckOpen = false"
                             @click.outside="if (! ($refs.popover && $refs.popover.contains($event.target))) ckOpen = false">

                            <button type="button"
                                    x-ref="trigger"
                                    @click="togglePopover($event.currentTarget)"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-sm rounded-lg border border-base-content/20 bg-base-100 hover:border-primary focus:border-primary focus:outline-none transition-colors"
                                    :class="{ 'border-primary': ckOpen }">
                                <span class="flex items-center gap-2 min-w-0 flex-1">
                                    {!! mrcatz_icon('filter_alt', 'text-base-content/50 shrink-0 w-4 h-4') !!}
                                    <span class="truncate text-left" x-text="triggerText()"></span>
                                </span>
                                <span class="flex items-center gap-1 shrink-0">
                                    <span x-show="hasValue()"
                                          role="button"
                                          tabindex="0"
                                          @click.stop="reset()"
                                          @keydown.enter.stop.prevent="reset()"
                                          @keydown.space.stop.prevent="reset()"
                                          class="hover:bg-base-200 rounded-full p-0.5 transition cursor-pointer inline-flex items-center justify-center"
                                          :title="labels.reset">
                                        {!! mrcatz_icon('close', 'text-base-content/40 w-3.5 h-3.5') !!}
                                    </span>
                                    {!! mrcatz_icon('expand_more', 'text-base-content/40 w-4 h-4') !!}
                                </span>
                            </button>

                            <template x-teleport="body">
                            <div x-show="ckOpen"
                                 x-ref="popover"
                                 style="display: none;"
                                 :style="{ top: popoverTop + 'px', left: popoverLeft + 'px' }"
                                 class="fixed z-[100] w-[20rem] bg-base-100 rounded-xl shadow-2xl border border-base-300 overflow-hidden flex flex-col">

                                {{-- Include / Exclude mode toggle (only when allowExclude).
                                     Active-state highlight reads `draftMode` so the user
                                     sees their in-progress pick, committed on Apply. --}}
                                <template x-if="allowExclude">
                                    <div class="flex items-stretch gap-1 p-2 border-b border-base-300 bg-base-200/50">
                                        <button type="button"
                                                @click="setMode('include')"
                                                class="flex-1 px-2 py-1 text-xs rounded transition-colors"
                                                :class="draftMode === 'include' ? 'bg-primary text-primary-content font-semibold' : 'text-base-content/60 hover:bg-base-300/50'"
                                                x-text="labels.include"></button>
                                        <button type="button"
                                                @click="setMode('exclude')"
                                                class="flex-1 px-2 py-1 text-xs rounded transition-colors"
                                                :class="draftMode === 'exclude' ? 'bg-error text-error-content font-semibold' : 'text-base-content/60 hover:bg-base-300/50'"
                                                x-text="labels.exclude"></button>
                                    </div>
                                </template>

                                {{-- Search box (only when option count > threshold) --}}
                                <template x-if="shouldShowSearch()">
                                    <div class="p-2 border-b border-base-300">
                                        <input type="text"
                                               x-ref="searchInput"
                                               x-model="search"
                                               :placeholder="labels.search"
                                               class="input input-bordered input-sm w-full text-sm">
                                    </div>
                                </template>

                                {{-- Option list --}}
                                <div class="max-h-[16rem] overflow-y-auto">
                                    <template x-for="opt in filteredOptions()" :key="opt._key">
                                        <label class="flex items-center gap-2 px-3 py-2 hover:bg-base-200 cursor-pointer text-sm">
                                            <input type="checkbox"
                                                   class="checkbox checkbox-sm checkbox-primary"
                                                   :checked="isChecked(opt._value)"
                                                   @change="toggle(opt._value)">
                                            <span class="flex-1 truncate" x-html="highlight(opt._label)"></span>
                                        </label>
                                    </template>
                                    <div x-show="filteredOptions().length === 0"
                                         class="px-3 py-6 text-center text-sm text-base-content/50"
                                         x-text="labels.noMatch.replace(':query', search)"></div>
                                </div>

                                {{-- Counter + select-all / clear shortcuts.
                                     `flex-wrap` lets the two groups stack vertically
                                     when the translated labels would otherwise collide
                                     with the counter (e.g. Indonesian "terpilih" +
                                     "Pilih semua" / "Hapus pilihan" are long). --}}
                                <div class="flex flex-wrap items-center justify-between gap-x-3 gap-y-1 px-3 py-2 border-t border-base-300 bg-base-200/30 text-xs">
                                    <span class="text-base-content/60 whitespace-nowrap" x-text="counterText()"></span>
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <button type="button" @click="selectAllVisible()" class="text-primary hover:underline whitespace-nowrap" x-text="labels.selectAll"></button>
                                        <span class="text-base-content/30">·</span>
                                        <button type="button" @click="clearVisible()" class="text-primary hover:underline whitespace-nowrap" x-text="labels.clear"></button>
                                    </div>
                                </div>

                                {{-- Action footer. Apply commits draft → applied +
                                     server; Reset clears both draft and applied and
                                     commits empty. Closing the popover via ESC or
                                     outside-click intentionally discards draft. --}}
                                <div class="flex gap-2 p-2 border-t border-base-300">
                                    <button type="button" @click="reset(); ckOpen = false" class="btn btn-ghost btn-sm flex-1" x-text="labels.reset"></button>
                                    <button type="button" @click="apply()" class="btn btn-primary btn-sm flex-1" x-text="labels.apply"></button>
                                </div>
                            </div>
                            </template>
                        </div>
                    </div>

                @else
                    {{-- Default: select dropdown (existing behavior, unchanged) --}}
                    <div class="w-full sm:w-auto sm:min-w-48" wire:show="filterShow[{{$f}}]">
                        <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>
                        <select class="select select-bordered select-sm w-full text-sm focus:select-primary transition-all duration-200"
                                id="{{$filter['id']."_".$prefix}}"
                                x-data
                                @change.debounce.{{ $filterDebounceDelay }}="$wire.change('{{$filter['id']}}', $event.target.value)">
                            @php
                                $currentValue = $activeFilterValues[$filter['id']] ?? null;
                                // "All" is selected only when value is truly unset (null / '') —
                                // legitimate falsy values like 0, '0', false should keep their option selected.
                                $isAllSelected = ($currentValue === null || $currentValue === '');
                            @endphp
                            <option value="{{ $default_filter_value }}" @selected($isAllSelected)>{{ mrcatz_lang('filter_all') }}</option>
                            @foreach($filterData[$f] as $data)
                                {{-- Loose == compares URL strings (e.g. '0') to data integers (e.g. 0) correctly --}}
                                <option value="{{ $data[$filter['value']] }}" @selected(!$isAllSelected && $currentValue == $data[$filter['value']])>{{ $data[$filter['option']] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Alpine.js component for the date range popover.

         Wrapped in @@assets so Livewire guarantees the script runs exactly
         once per page — including when this blade is rendered inside a
         lazy-loaded Livewire component. A plain <script> tag inside
         morphed HTML does NOT execute (browsers only run script tags on
         initial parse, not on DOM insertion), so without @@assets the
         popover's x-data factory never gets registered on docs pages
         that use <livewire:... lazy /> and every click becomes a no-op. --}}
    @assets
    <script>
        if (typeof window.mrcatzDateRange === 'undefined') {
            window.mrcatzDateRange = function (config) {
                const fmt = (d) => {
                    if (!(d instanceof Date) || isNaN(d)) return '';
                    const y = d.getFullYear();
                    const m = String(d.getMonth() + 1).padStart(2, '0');
                    const dd = String(d.getDate()).padStart(2, '0');
                    return `${y}-${m}-${dd}`;
                };

                return {
                    // Renamed from `open` to avoid shadowing the outer filter
                    // panel's `open` x-data property. When Livewire morphs a
                    // lazy-loaded component, Alpine scope chain resolution
                    // for teleported popovers can resolve `open` to the PARENT
                    // scope (toolbar panel open state), making the popover
                    // appear whenever the filter toolbar is open.
                    drOpen: false,
                    from: config.from || '',
                    to: config.to || '',
                    draftFrom: config.from || '',
                    draftTo: config.to || '',
                    min: config.min || '',
                    max: config.max || '',
                    filterId: config.filterId,
                    labels: config.labels,
                    activePreset: null,
                    // Numeric top/left so :style can use object syntax —
                    // STRING :style overwrites the whole style attribute and
                    // wipes x-show's display:none, making the popover visible
                    // at position:fixed default (0,0) before open is true.
                    // Default off-screen as a safety net for any timing gap.
                    popoverTop: -9999,
                    popoverLeft: -9999,

                    presets: [
                        { key: 'today',      label: config.labels.today      },
                        { key: 'yesterday',  label: config.labels.yesterday  },
                        { key: 'last_7',     label: config.labels.last_7     },
                        { key: 'last_30',    label: config.labels.last_30    },
                        { key: 'this_month', label: config.labels.this_month },
                        { key: 'last_6m',    label: config.labels.last_6m    },
                        { key: 'this_year',  label: config.labels.this_year  },
                        { key: 'last_year',  label: config.labels.last_year  },
                    ],

                    init() {
                        // Sync drafts when popover opens + install scroll/resize
                        // handlers that reposition the teleported popover.
                        this.$watch('drOpen', (v) => {
                            if (v) {
                                this.draftFrom = this.from;
                                this.draftTo = this.to;
                                this._reposition = () => this.positionPopover();
                                window.addEventListener('scroll', this._reposition, true);
                                window.addEventListener('resize', this._reposition);
                            } else if (this._reposition) {
                                window.removeEventListener('scroll', this._reposition, true);
                                window.removeEventListener('resize', this._reposition);
                                this._reposition = null;
                            }
                        });
                    },

                    _triggerEl: null, // last-known trigger element for re-positioning

                    togglePopover(el) {
                        // Remember the clicked trigger — used by scroll/resize
                        // re-positioning. Falls back to $refs / querySelector
                        // only for defensive paths.
                        if (el) this._triggerEl = el;
                        // Compute position BEFORE flipping open — otherwise the
                        // popover renders for one frame with no top/left set
                        // (position:fixed defaults to 0,0 = top-left of viewport),
                        // and the entrance transition animates in from there
                        // instead of from just below the trigger.
                        if (!this.drOpen) this.computePosition();
                        this.drOpen = !this.drOpen;
                        // Re-run after next tick as a safety net for lazy-load
                        // / morph edge cases.
                        if (this.drOpen) this.$nextTick(() => this.computePosition());
                    },

                    // Resolve trigger from: cached event element → $refs → DOM query.
                    // $refs can be momentarily undefined during Livewire morphs
                    // following lazy-hydration, so we never rely on it alone.
                    _getTrigger() {
                        return this._triggerEl
                            || this.$refs.trigger
                            || (this.$el && this.$el.querySelector('[x-ref="trigger"]'));
                    },

                    computePosition() {
                        const trigger = this._getTrigger();
                        if (!trigger) return;
                        const rect = trigger.getBoundingClientRect();
                        const pw = 352; // w-[22rem] = 22 * 16
                        const gap = 4;
                        const margin = 8;

                        // Prefer the real measured height once the popover has
                        // rendered. Falls back to an approximation on the very
                        // first paint before mount — the $nextTick refine pass
                        // in togglePopover replaces it with offsetHeight so the
                        // flip-above case nests snugly against the trigger.
                        const pop = this.$refs && this.$refs.popover;
                        const ph = (pop && pop.offsetHeight > 0) ? pop.offsetHeight : 340;

                        let top = rect.bottom + gap;
                        let left = rect.left;

                        // Flip above trigger if it would overflow viewport bottom
                        if (top + ph > window.innerHeight - margin) {
                            top = Math.max(margin, rect.top - ph - gap);
                        }
                        // Shift left if it would overflow viewport right
                        if (left + pw > window.innerWidth - margin) {
                            left = window.innerWidth - pw - margin;
                        }
                        if (left < margin) left = margin;

                        this.popoverTop = top;
                        this.popoverLeft = left;
                    },

                    // Legacy alias kept for the scroll/resize handler wiring
                    positionPopover() { this.computePosition(); },

                    hasValue() {
                        return !!(this.from || this.to);
                    },

                    triggerText() {
                        if (!this.from && !this.to) return this.labels.pick;
                        const f = this.from || '…';
                        const t = this.to   || '…';
                        return `${this.labels.from} ${f}  →  ${this.labels.to} ${t}`;
                    },

                    applyPreset(key) {
                        const today = new Date(); today.setHours(0, 0, 0, 0);
                        let from = null, to = null;

                        switch (key) {
                            case 'today':
                                from = to = new Date(today);
                                break;
                            case 'yesterday':
                                from = new Date(today); from.setDate(from.getDate() - 1);
                                to = new Date(from);
                                break;
                            case 'last_7':
                                to = new Date(today);
                                from = new Date(today); from.setDate(from.getDate() - 6);
                                break;
                            case 'last_30':
                                to = new Date(today);
                                from = new Date(today); from.setDate(from.getDate() - 29);
                                break;
                            case 'this_month':
                                from = new Date(today.getFullYear(), today.getMonth(), 1);
                                to = new Date(today);
                                break;
                            case 'last_6m':
                                to = new Date(today);
                                from = new Date(today); from.setMonth(from.getMonth() - 6);
                                break;
                            case 'this_year':
                                from = new Date(today.getFullYear(), 0, 1);
                                to = new Date(today);
                                break;
                            case 'last_year':
                                to = new Date(today);
                                from = new Date(today); from.setFullYear(from.getFullYear() - 1);
                                break;
                        }

                        this.draftFrom = fmt(from);
                        this.draftTo = fmt(to);
                        this.activePreset = key;
                    },

                    apply() {
                        // Auto-swap if from > to
                        if (this.draftFrom && this.draftTo && this.draftFrom > this.draftTo) {
                            [this.draftFrom, this.draftTo] = [this.draftTo, this.draftFrom];
                        }
                        this.from = this.draftFrom;
                        this.to = this.draftTo;
                        this.drOpen = false;

                        // Push both halves to Livewire — separate calls so server-side
                        // changeDateRange handles each part with its existing clamp + swap logic
                        this.$wire.changeDateRange(this.filterId, 'from', this.from);
                        this.$wire.changeDateRange(this.filterId, 'to',   this.to);
                    },

                    clear() {
                        // Optimistic UI update — instant feedback before the
                        // server roundtrip. The wire:key on the wrapper includes
                        // a hash of the value, so once resetFilter completes the
                        // morph will give this element a new key, Livewire will
                        // recreate it, and Alpine will re-init with fresh empty
                        // values from the server. We don't need to fight the
                        // morph here — just paint the UI optimistically.
                        this.from = '';
                        this.to = '';
                        this.draftFrom = '';
                        this.draftTo = '';
                        this.activePreset = null;

                        // Force native input clear too. Some browsers don't
                        // reflect a programmatic '' via x-model on date inputs.
                        ['fromInput', 'toInput'].forEach((ref) => {
                            const el = this.$refs[ref];
                            if (el) {
                                el.value = '';
                                el.dispatchEvent(new Event('input',  { bubbles: true }));
                                el.dispatchEvent(new Event('change', { bubbles: true }));
                            }
                        });

                        // Single atomic server call — clears the filter entirely
                        // (no intermediate ['from'=>null,'to'=>OLD] state).
                        this.$wire.resetFilter(this.filterId);
                    },
                };
            };
        }

        if (typeof window.mrcatzCheckFilter === 'undefined') {
            window.mrcatzCheckFilter = function (config) {
                // Build once per mount — we index options by a stable key so
                // x-for reconciles cleanly across re-renders. Values that
                // collide on their raw key get a positional suffix so Alpine
                // never sees duplicate :key values.
                const seen = new Map();
                const options = (config.options || []).map((o, i) => {
                    const rawV = o[config.valueKey];
                    const rawL = o[config.optionKey];
                    const k0 = String(rawV ?? '__null__');
                    const n = (seen.get(k0) ?? -1) + 1;
                    seen.set(k0, n);
                    return {
                        _key:   n === 0 ? k0 : `${k0}__${n}`,
                        _value: rawV,
                        _label: String(rawL ?? ''),
                    };
                });

                // Escape a string for safe HTML interpolation.
                const escapeHtml = (s) => String(s).replace(/[&<>"']/g, (c) => ({
                    '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
                }[c]));

                return {
                    ckOpen: false,
                    // APPLIED state — mirrors server's activeFilters entry and
                    // is what the trigger button displays. Only mutates when
                    // Apply / Reset commits to the server.
                    values: Array.isArray(config.values) ? [...config.values] : [],
                    mode: config.mode === 'exclude' ? 'exclude' : 'include',
                    // DRAFT state — user's in-progress selection inside the
                    // popover. Synced FROM applied on popover open; the user's
                    // check/uncheck/mode-toggle actions mutate only the draft
                    // until they click Apply.
                    draftValues: Array.isArray(config.values) ? [...config.values] : [],
                    draftMode: config.mode === 'exclude' ? 'exclude' : 'include',
                    options,
                    allowExclude: !!config.allowExclude,
                    searchThreshold: (config.searchThreshold === undefined) ? null : config.searchThreshold,
                    search: '',
                    filterId: config.filterId,
                    labels: config.labels,
                    // Off-screen until first computePosition runs — same
                    // technique as mrcatzDateRange to avoid a 0,0 flash
                    // during the x-show transition.
                    popoverTop: -9999,
                    popoverLeft: -9999,
                    _triggerEl: null,
                    _reposition: null,

                    init() {
                        this.$watch('ckOpen', (v) => {
                            if (v) {
                                // Opening: refresh draft from applied so any
                                // previously discarded draft is forgotten.
                                this.draftValues = [...this.values];
                                this.draftMode = this.mode;
                                this.search = '';
                                this._reposition = () => this.positionPopover();
                                window.addEventListener('scroll', this._reposition, true);
                                window.addEventListener('resize', this._reposition);
                                this.$nextTick(() => {
                                    if (this.shouldShowSearch() && this.$refs.searchInput) {
                                        this.$refs.searchInput.focus();
                                    }
                                });
                            } else if (this._reposition) {
                                window.removeEventListener('scroll', this._reposition, true);
                                window.removeEventListener('resize', this._reposition);
                                this._reposition = null;
                            }
                        });
                        // Server-state sync is handled by wire:key: any change
                        // to the APPLIED value/mode changes the key, so Livewire
                        // recreates the element and this factory runs again with
                        // fresh config from the blade render. No in-Alpine
                        // watcher on $wire.activeFilters is needed.
                    },

                    togglePopover(el) {
                        if (el) this._triggerEl = el;
                        if (!this.ckOpen) this.computePosition();
                        this.ckOpen = !this.ckOpen;
                        if (this.ckOpen) this.$nextTick(() => this.computePosition());
                    },

                    _getTrigger() {
                        return this._triggerEl
                            || this.$refs.trigger
                            || (this.$el && this.$el.querySelector('[x-ref="trigger"]'));
                    },

                    // Estimate popover height from the actual widget config —
                    // much closer to reality than a flat constant. Used only
                    // for the first paint; the refine pass in togglePopover's
                    // $nextTick replaces it with a real offsetHeight measurement.
                    _estimateHeight() {
                        const modeH   = this.allowExclude       ? 40 : 0;
                        const searchH = this.shouldShowSearch() ? 48 : 0;
                        const optH    = 36;  // single checkbox row height
                        const listH   = Math.min(this.options.length * optH, 256); // cap at max-h-[16rem]
                        const counterH = 32;
                        const footerH  = 48;
                        return modeH + searchH + listH + counterH + footerH;
                    },

                    computePosition() {
                        const trigger = this._getTrigger();
                        if (!trigger) return;
                        const rect = trigger.getBoundingClientRect();
                        const pw = 320; // w-[20rem] = 20 * 16
                        const gap = 4;
                        const margin = 8;

                        // Prefer the real measured height once the popover has
                        // rendered. Falls back to the config-aware estimator on
                        // the very first paint before mount.
                        const pop = this.$refs && this.$refs.popover;
                        const ph = (pop && pop.offsetHeight > 0)
                            ? pop.offsetHeight
                            : this._estimateHeight();

                        let top = rect.bottom + gap;
                        let left = rect.left;

                        if (top + ph > window.innerHeight - margin) {
                            top = Math.max(margin, rect.top - ph - gap);
                        }
                        if (left + pw > window.innerWidth - margin) {
                            left = window.innerWidth - pw - margin;
                        }
                        if (left < margin) left = margin;

                        this.popoverTop = top;
                        this.popoverLeft = left;
                    },

                    positionPopover() { this.computePosition(); },

                    // Trigger button state — based on APPLIED values, so the
                    // chip reflects what's actually filtering right now.
                    hasValue() { return this.values.length > 0; },

                    shouldShowSearch() {
                        if (this.searchThreshold === null) return false;
                        return this.options.length > this.searchThreshold;
                    },

                    // Checkbox visual state reflects DRAFT so the user sees
                    // their in-progress picks, not the applied set.
                    isChecked(value) {
                        return this.draftValues.some((v) => v == value);
                    },

                    filteredOptions() {
                        const q = this.search.trim().toLowerCase();
                        if (!q) return this.options;
                        return this.options.filter((o) => o._label.toLowerCase().includes(q));
                    },

                    // --- Draft mutations (no server roundtrip) ---

                    toggle(value) {
                        const idx = this.draftValues.findIndex((v) => v == value);
                        if (idx >= 0) {
                            this.draftValues.splice(idx, 1);
                        } else {
                            this.draftValues.push(value);
                        }
                    },

                    selectAllVisible() {
                        const visible = this.filteredOptions();
                        const next = [...this.draftValues];
                        visible.forEach((opt) => {
                            if (!next.some((v) => v == opt._value)) next.push(opt._value);
                        });
                        this.draftValues = next;
                    },

                    clearVisible() {
                        // Empty search → clear whole draft. Active search →
                        // clear only the currently visible subset so the
                        // user's filtered intent is preserved.
                        if (this.search.trim() === '') {
                            this.draftValues = [];
                        } else {
                            const visible = new Set(
                                this.filteredOptions().map((o) => String(o._value))
                            );
                            this.draftValues = this.draftValues.filter((v) => !visible.has(String(v)));
                        }
                    },

                    setMode(mode) {
                        if (mode !== 'include' && mode !== 'exclude') return;
                        this.draftMode = mode;
                    },

                    // --- Commit paths (server roundtrip) ---

                    // Apply: push draft → applied → server, then close.
                    // Uses a single $wire.applyCheck call so the engine runs
                    // findData() exactly once per commit.
                    apply() {
                        this.values = [...this.draftValues];
                        this.mode = this.draftMode;
                        this.$wire.applyCheck(
                            this.filterId,
                            this.values,
                            this.allowExclude ? this.mode : null,
                        );
                        this.ckOpen = false;
                    },

                    // Reset: clear applied + draft AND commit empty state.
                    // Used by the trigger's clear-x and the popover's Reset
                    // button. resetFilter() on the server nulls the value
                    // outright, so the filter is dropped from activeFilters.
                    reset() {
                        this.values = [];
                        this.draftValues = [];
                        this.mode = 'include';
                        this.draftMode = 'include';
                        this.search = '';
                        this.$wire.resetFilter(this.filterId);
                    },

                    // XSS-safe highlight: match on the raw label, then escape
                    // each chunk separately — the only unescaped HTML we emit
                    // is the <mark> tag itself, which we control.
                    highlight(label) {
                        const q = this.search.trim();
                        if (!q) return escapeHtml(label);

                        const lower = label.toLowerCase();
                        const qLower = q.toLowerCase();

                        let out = '';
                        let i = 0;
                        while (i < label.length) {
                            const idx = lower.indexOf(qLower, i);
                            if (idx === -1) {
                                out += escapeHtml(label.slice(i));
                                break;
                            }
                            out += escapeHtml(label.slice(i, idx));
                            out += '<mark class="bg-warning/40 text-base-content rounded px-0.5">'
                                 + escapeHtml(label.slice(idx, idx + q.length))
                                 + '</mark>';
                            i = idx + q.length;
                        }
                        return out;
                    },

                    triggerText() {
                        if (this.values.length === 0) return this.labels.pick;
                        const names = this.values.map((v) => {
                            const opt = this.options.find((o) => o._value == v);
                            return opt ? opt._label : String(v);
                        });
                        let txt;
                        if (names.length <= 2) {
                            txt = names.join(', ');
                        } else {
                            txt = names[0] + ' ' + this.labels.plusMore.replace(':count', names.length - 1);
                        }
                        return this.mode === 'exclude'
                            ? this.labels.notPrefix + txt
                            : txt;
                    },

                    // Counter reads DRAFT so it feedback-matches what the
                    // user is ticking in real-time, not the applied state.
                    counterText() {
                        return this.labels.selected
                            .replace(':count', this.draftValues.length)
                            .replace(':total', this.options.length);
                    },
                };
            };
        }
    </script>
    @endassets
@endif
