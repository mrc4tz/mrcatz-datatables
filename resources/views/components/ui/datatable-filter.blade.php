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
                        hover: -1,
                        selected: '{{ $currentVal }}',
                        selectedLabel: '{{ addslashes($currentLabel) }}',
                        filterId: '{{ $filter['id'] }}',
                        pick(val, label) {
                            this.selected = val;
                            this.selectedLabel = label;
                            $wire.change(this.filterId, val);
                            this.close();
                        },
                        close() { this.isOpen = false; this.query = ''; this.hover = -1; }
                     }">
                    <label class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-1 block">{{ $filter['label'] }}</label>
                    <div style="position:relative;">
                        {{-- Trigger --}}
                        <div @click="isOpen = !isOpen" @click.outside="close()" @keydown.escape="close()"
                             style="display:flex;align-items:center;justify-content:space-between;gap:8px;width:100%;height:2rem;padding:0 0.75rem;font-size:0.8125rem;line-height:1.25rem;cursor:pointer;border-radius:var(--rounded-btn);border:1px solid color-mix(in srgb,var(--color-base-content) 20%,transparent);background:var(--color-base-100);color:var(--color-base-content);user-select:none;">
                            <span x-text="selectedLabel" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap;flex:1;"></span>
                            <span class="material-icons" style="font-size:1rem;opacity:.3;transition:transform .2s;" :style="isOpen ? 'transform:rotate(180deg)' : ''">expand_more</span>
                        </div>

                        {{-- Dropdown --}}
                        <div x-show="isOpen" x-transition:enter="transition ease-out duration-100"
                             x-transition:enter-start="opacity-0 translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                             style="position:absolute;z-index:50;width:100%;min-width:220px;margin-top:6px;border-radius:0.75rem;border:1px solid color-mix(in srgb,var(--color-base-content) 10%,transparent);background:var(--color-base-100);box-shadow:0 10px 30px -5px rgba(0,0,0,.12);overflow:hidden;">

                            {{-- Search --}}
                            <div style="padding:8px 8px 6px;">
                                <div style="display:flex;align-items:center;gap:6px;padding:0 0.625rem;height:2rem;border-radius:var(--rounded-btn);border:1px solid color-mix(in srgb,var(--color-base-content) 12%,transparent);background:color-mix(in srgb,var(--color-base-content) 3%,transparent);">
                                    <span class="material-icons" style="font-size:0.875rem;opacity:.25;">search</span>
                                    <input type="text" x-model="query" x-ref="filterSearch" @click.stop
                                           x-init="$watch('isOpen', v => { if(v) $nextTick(() => $refs.filterSearch.focus()) })"
                                           placeholder="Cari..."
                                           style="width:100%;font-size:0.75rem;border:none;outline:none;background:transparent;color:var(--color-base-content);" />
                                </div>
                            </div>

                            {{-- Options --}}
                            <div style="max-height:180px;overflow-y:auto;padding:0 6px 6px;display:flex;flex-direction:column;gap:1px;">
                                <div @click="pick('', 'Semua')" @mouseenter="hover = 0" @mouseleave="hover = -1"
                                     x-show="'semua'.includes(query.toLowerCase()) || query === ''"
                                     :style="selected === ''
                                        ? 'background:color-mix(in srgb,var(--color-primary) 10%,transparent);color:var(--color-primary);font-weight:500'
                                        : hover === 0
                                            ? 'background:color-mix(in srgb,var(--color-base-content) 5%,transparent)'
                                            : ''"
                                     style="padding:0.375rem 0.75rem;font-size:0.75rem;line-height:1.25rem;border-radius:0.375rem;cursor:pointer;">
                                    Semua
                                </div>

                                @foreach($filterData[$f] as $idx => $data)
                                    <div @click="pick('{{ addslashes($data[$filter['value']]) }}', '{{ addslashes($data[$filter['option']]) }}')"
                                         @mouseenter="hover = {{ $idx + 1 }}" @mouseleave="hover = -1"
                                         x-show="'{{ strtolower(addslashes($data[$filter['option']])) }}'.includes(query.toLowerCase()) || query === ''"
                                         :style="selected === '{{ addslashes($data[$filter['value']]) }}'
                                            ? 'background:color-mix(in srgb,var(--color-primary) 10%,transparent);color:var(--color-primary);font-weight:500'
                                            : hover === {{ $idx + 1 }}
                                                ? 'background:color-mix(in srgb,var(--color-base-content) 5%,transparent)'
                                                : ''"
                                         style="padding:0.375rem 0.75rem;font-size:0.75rem;line-height:1.25rem;border-radius:0.375rem;cursor:pointer;">
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
