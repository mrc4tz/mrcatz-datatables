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
                               wire:change="change('{{$filter['id']}}', $event.target.value)">
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

                        {{-- wire:key includes a hash of the current value so that
                             ANY value change (user X click, global reset, external
                             setFilterShow / resetFilter, etc.) gives the element a
                             new identity. Livewire then destroys + recreates it,
                             which forces Alpine to re-initialise with the fresh
                             @ js values from the server. This is the only way to
                             keep client Alpine state in sync with server state
                             across BOTH local interactions AND external resets. --}}
                        <div class="relative"
                             wire:key="dr-{{ $filter['id'] }}-{{ md5($rangeFrom . '|' . $rangeTo) }}"
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
                             @keydown.escape.window="open = false"
                             @click.outside="open = false">

                            {{-- Clickable trigger.
                                 NOTE: must NOT contain a real <button> child — nested
                                 <button> is invalid HTML and browsers will close the
                                 outer button early, dropping subsequent children to a
                                 new line. The clear "x" is therefore a span with
                                 role=button for screen readers + click.stop. --}}
                            <button type="button"
                                    @click="open = !open"
                                    class="w-full flex items-center justify-between gap-2 px-3 py-1.5 text-sm rounded-lg border border-base-content/20 bg-base-100 hover:border-primary focus:border-primary focus:outline-none transition-colors"
                                    :class="{ 'border-primary': open }">
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

                            {{-- Popover --}}
                            <div x-show="open"
                                 x-transition:enter="transition ease-out duration-150"
                                 x-transition:enter-start="opacity-0 -translate-y-1"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100"
                                 x-transition:leave-start="opacity-100"
                                 x-transition:leave-end="opacity-0"
                                 class="absolute z-50 mt-2 w-[22rem] bg-base-100 rounded-xl shadow-2xl border border-base-300 overflow-hidden"
                                 x-cloak>

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
                                                    @click="clear(); open = false"
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
                        </div>
                    </div>

                @else
                    {{-- Default: select dropdown (existing behavior, unchanged) --}}
                    <div class="w-full sm:w-auto sm:min-w-48" wire:show="filterShow[{{$f}}]">
                        <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>
                        <select class="select select-bordered select-sm w-full text-sm focus:select-primary transition-all duration-200"
                                id="{{$filter['id']."_".$prefix}}"
                                wire:change="change('{{$filter['id']}}',$event.target.value)">
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

    {{-- Alpine.js component for the date range popover --}}
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
                    open: false,
                    from: config.from || '',
                    to: config.to || '',
                    draftFrom: config.from || '',
                    draftTo: config.to || '',
                    min: config.min || '',
                    max: config.max || '',
                    filterId: config.filterId,
                    labels: config.labels,
                    activePreset: null,

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
                        // Sync drafts when popover opens
                        this.$watch('open', (v) => {
                            if (v) {
                                this.draftFrom = this.from;
                                this.draftTo = this.to;
                            }
                        });
                    },

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
                        this.open = false;

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
    </script>
@endif
