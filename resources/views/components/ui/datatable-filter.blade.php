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
                        selectedLabel: '{{ addslashes($currentLabel) }}',
                        filterId: '{{ $filter['id'] }}',
                        pick(val, label) {
                            this.selected = val;
                            this.selectedLabel = label;
                            $wire.change(this.filterId, val);
                            this.close();
                        },
                        close() { this.isOpen = false; this.query = ''; }
                     }">
                    <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>
                    <div style="position:relative;">
                        {{-- Trigger button --}}
                        <div @click="isOpen = !isOpen" @click.outside="close()" @keydown.escape="close()"
                             style="display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%;height:32px;padding:0 12px;font-size:13px;cursor:pointer;border-radius:var(--rounded-btn);border:1px solid color-mix(in srgb,var(--color-base-content) 15%,transparent);background:var(--color-base-100);color:var(--color-base-content);user-select:none;">
                            <span x-text="selectedLabel" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;"></span>
                            <span class="material-icons" style="font-size:16px;opacity:.3;transition:transform .2s;" :style="isOpen ? 'transform:rotate(180deg)' : ''">expand_more</span>
                        </div>

                        {{-- Dropdown panel --}}
                        <div x-show="isOpen" x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             style="position:absolute;z-index:50;width:100%;min-width:200px;margin-top:6px;border-radius:0.75rem;border:1px solid color-mix(in srgb,var(--color-base-content) 10%,transparent);background:var(--color-base-100);box-shadow:0 10px 30px -5px rgba(0,0,0,.12);overflow:hidden;">

                            {{-- Search --}}
                            <div style="padding:8px 8px 4px;">
                                <div style="display:flex;align-items:center;gap:6px;padding:0 8px;height:30px;border-radius:0.5rem;border:1px solid color-mix(in srgb,var(--color-base-content) 12%,transparent);background:color-mix(in srgb,var(--color-base-content) 3%,transparent);">
                                    <span class="material-icons" style="font-size:14px;opacity:.25;">search</span>
                                    <input type="text" x-model="query" x-ref="filterSearch" @click.stop
                                           x-init="$watch('isOpen', v => { if(v) $nextTick(() => $refs.filterSearch.focus()) })"
                                           placeholder="Cari..."
                                           style="width:100%;font-size:12px;border:none;outline:none;background:transparent;color:var(--color-base-content);" />
                                </div>
                            </div>

                            {{-- Options list --}}
                            <div style="max-height:180px;overflow-y:auto;padding:4px 6px 8px;display:flex;flex-direction:column;gap:2px;">
                                {{-- Semua --}}
                                <div @click="pick('', 'Semua')"
                                     x-show="'semua'.includes(query.toLowerCase()) || query === ''"
                                     :style="selected === '' ? 'color:var(--color-primary);font-weight:600;background:color-mix(in srgb,var(--color-primary) 8%,transparent)' : ''"
                                     style="padding:7px 12px;font-size:13px;border-radius:0.5rem;cursor:pointer;transition:background .1s;"
                                     onmouseenter="if(!this.style.fontWeight)this.style.background='color-mix(in srgb,var(--color-base-content) 5%,transparent)'"
                                     onmouseleave="if(!this.style.fontWeight)this.style.background=''">
                                    Semua
                                </div>

                                @foreach($filterData[$f] as $data)
                                    <div @click="pick('{{ addslashes($data[$filter['value']]) }}', '{{ addslashes($data[$filter['option']]) }}')"
                                         x-show="'{{ strtolower(addslashes($data[$filter['option']])) }}'.includes(query.toLowerCase()) || query === ''"
                                         :style="selected === '{{ addslashes($data[$filter['value']]) }}' ? 'color:var(--color-primary);font-weight:600;background:color-mix(in srgb,var(--color-primary) 8%,transparent)' : ''"
                                         style="padding:7px 12px;font-size:13px;border-radius:0.5rem;cursor:pointer;transition:background .1s;"
                                         onmouseenter="if(!this.style.fontWeight)this.style.background='color-mix(in srgb,var(--color-base-content) 5%,transparent)'"
                                         onmouseleave="if(!this.style.fontWeight)this.style.background=''">
                                        {{ $data[$filter['option']] }}
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
