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
                        $htmlType = $dateInputType($filter['format'] ?? 'date');
                        $rangeVal = $activeFilterValues[$filter['id']] ?? [];
                        $rangeFrom = is_array($rangeVal) ? ($rangeVal['from'] ?? '') : '';
                        $rangeTo   = is_array($rangeVal) ? ($rangeVal['to']   ?? '') : '';
                        $minAttr   = $filter['min_date'] ?? '';
                        $maxAttr   = $filter['max_date'] ?? '';
                    @endphp
                    <div class="w-full sm:w-auto" wire:show="filterShow[{{$f}}]">
                        <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>
                        <div class="flex gap-2 items-center"
                             x-data="{ from: @js($rangeFrom), to: @js($rangeTo) }">
                            <input type="{{ $htmlType }}"
                                   class="input input-bordered input-sm text-sm focus:input-primary"
                                   id="{{$filter['id']."_from_".$prefix}}"
                                   x-model="from"
                                   @if($minAttr) min="{{ $minAttr }}" @endif
                                   :max="to || @js($maxAttr)"
                                   wire:change="changeDateRange('{{$filter['id']}}', 'from', $event.target.value)">
                            <span class="text-xs text-base-content/50">{{ mrcatz_lang('filter_range_separator') ?? '—' }}</span>
                            <input type="{{ $htmlType }}"
                                   class="input input-bordered input-sm text-sm focus:input-primary"
                                   id="{{$filter['id']."_to_".$prefix}}"
                                   x-model="to"
                                   :min="from || @js($minAttr)"
                                   @if($maxAttr) max="{{ $maxAttr }}" @endif
                                   wire:change="changeDateRange('{{$filter['id']}}', 'to', $event.target.value)">
                        </div>
                    </div>

                @else
                    {{-- Default: select dropdown (existing behavior, unchanged) --}}
                    <div class="w-full sm:w-auto sm:min-w-48" wire:show="filterShow[{{$f}}]">
                        <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>
                        <select class="select select-bordered select-sm w-full text-sm focus:select-primary transition-all duration-200"
                                id="{{$filter['id']."_".$prefix}}"
                                wire:change="change('{{$filter['id']}}',$event.target.value)">
                            <option value="{{ $default_filter_value }}" @selected(empty($activeFilterValues[$filter['id']] ?? null))>{{ mrcatz_lang('filter_all') }}</option>
                            @foreach($filterData[$f] as $data)
                                <option value="{{ $data[$filter['value']] }}" @selected(($activeFilterValues[$filter['id']] ?? null) === $data[$filter['value']])>{{ $data[$filter['option']] }}</option>
                            @endforeach
                        </select>
                    </div>
                @endif
            @endforeach
        </div>
    </div>
@endif
