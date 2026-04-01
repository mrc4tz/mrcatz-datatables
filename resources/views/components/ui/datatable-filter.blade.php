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
                @php
                    $currentVal = $activeFilterValues[$filter['id']] ?? null;
                    $currentLabel = 'Semua';
                    foreach($filterData[$f] as $d) {
                        if (($d[$filter['value']] ?? null) === $currentVal) {
                            $currentLabel = $d[$filter['option']];
                            break;
                        }
                    }
                @endphp
                <div class="w-full sm:w-auto sm:min-w-48" wire:show="filterShow[{{$f}}]"
                     x-data="{
                        isOpen: false,
                        query: '',
                        selected: '{{ $currentVal }}',
                        selectedLabel: '{{ $currentLabel }}',
                        filterId: '{{ $filter['id'] }}',
                        close() { this.isOpen = false; this.query = ''; }
                     }">
                    <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>
                    <div class="relative">
                        {{-- Trigger --}}
                        <button type="button" @click="isOpen = !isOpen"
                                @click.outside="close()"
                                @keydown.escape="close()"
                                class="select select-bordered select-sm w-full text-sm text-left flex items-center justify-between"
                                style="cursor:pointer;">
                            <span x-text="selectedLabel" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"></span>
                        </button>

                        {{-- Dropdown --}}
                        <div x-show="isOpen"
                             x-transition:enter="transition ease-out duration-150"
                             x-transition:enter-start="opacity-0 scale-95"
                             x-transition:enter-end="opacity-100 scale-100"
                             x-transition:leave="transition ease-in duration-100"
                             x-transition:leave-start="opacity-100 scale-100"
                             x-transition:leave-end="opacity-0 scale-95"
                             style="position:absolute;z-index:50;width:100%;margin-top:4px;background:var(--color-base-100);border:1px solid color-mix(in srgb,var(--color-base-content) 10%,transparent);border-radius:0.75rem;box-shadow:0 10px 25px -5px rgba(0,0,0,.1);overflow:hidden;">

                            {{-- Search input --}}
                            <div style="padding:8px;">
                                <input type="text" x-model="query" x-ref="filterSearch"
                                       @click.stop
                                       x-init="$watch('isOpen', v => { if(v) $nextTick(() => $refs.filterSearch.focus()) })"
                                       placeholder="Cari..."
                                       style="width:100%;padding:6px 10px;font-size:13px;border:1px solid color-mix(in srgb,var(--color-base-content) 15%,transparent);border-radius:0.5rem;outline:none;background:transparent;color:var(--color-base-content);" />
                            </div>

                            {{-- Options --}}
                            <div style="max-height:200px;overflow-y:auto;padding:0 4px 4px;">
                                {{-- Option: Semua --}}
                                <button type="button"
                                        x-show="'semua'.includes(query.toLowerCase()) || query === ''"
                                        @click="selected = ''; selectedLabel = 'Semua'; $wire.change(filterId, ''); close();"
                                        style="width:100%;text-align:left;padding:6px 10px;font-size:13px;border-radius:0.375rem;cursor:pointer;transition:background 0.1s;"
                                        onmouseenter="this.style.background='color-mix(in srgb,var(--color-base-content) 8%,transparent)'"
                                        onmouseleave="this.style.background='transparent'"
                                        :style="selected === '' ? 'font-weight:600;color:var(--color-primary)' : 'color:var(--color-base-content)'">
                                    Semua
                                </button>

                                @foreach($filterData[$f] as $data)
                                    <button type="button"
                                            x-show="'{{ strtolower($data[$filter['option']]) }}'.includes(query.toLowerCase()) || query === ''"
                                            @click="selected = '{{ $data[$filter['value']] }}'; selectedLabel = '{{ $data[$filter['option']] }}'; $wire.change(filterId, '{{ $data[$filter['value']] }}'); close();"
                                            style="width:100%;text-align:left;padding:6px 10px;font-size:13px;border-radius:0.375rem;cursor:pointer;transition:background 0.1s;"
                                            onmouseenter="this.style.background='color-mix(in srgb,var(--color-base-content) 8%,transparent)'"
                                            onmouseleave="this.style.background='transparent'"
                                            :style="selected === '{{ $data[$filter['value']] }}' ? 'font-weight:600;color:var(--color-primary)' : 'color:var(--color-base-content)'">
                                        {{ $data[$filter['option']] }}
                                    </button>
                                @endforeach

                                {{-- No results --}}
                                <div x-show="query !== '' && !document.querySelector('[x-show*=query]')"
                                     style="padding:8px 10px;font-size:12px;color:color-mix(in srgb,var(--color-base-content) 30%,transparent);text-align:center;">
                                    Tidak ditemukan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
