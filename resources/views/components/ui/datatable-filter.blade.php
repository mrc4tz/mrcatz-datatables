@if(count($dataFilters) > 0)
    @php $activeFilterValues = collect($activeFilters ?? [])->pluck('value', 'id')->toArray(); @endphp
    <div x-show="open" x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         class="p-4 mb-4 rounded-xl bg-base-200/50 border border-base-content/10" id="dtFilter">
        <div class="flex flex-wrap gap-4">
            @foreach($dataFilters as $f => $filter)
                <div class="w-full sm:w-auto sm:min-w-48" wire:show="filterShow[{{$f}}]">
                    <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>
                    <select class="select select-bordered select-sm w-full text-sm focus:select-primary transition-all duration-200"
                            id="{{$filter['id']."_".$prefix}}"
                            wire:change="change('{{$filter['id']}}',$event.target.value)">
                        <option value="{{ $default_filter_value }}" @selected(empty($activeFilterValues[$filter['id']] ?? null))>Semua</option>
                        @foreach($filterData[$f] as $data)
                            <option value="{{ $data[$filter['value']] }}" @selected(($activeFilterValues[$filter['id']] ?? null) === $data[$filter['value']])>{{ $data[$filter['option']] }}</option>
                        @endforeach
                    </select>
                </div>
            @endforeach
        </div>
    </div>
@endif
