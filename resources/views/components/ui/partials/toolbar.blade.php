{{-- Toolbar --}}

{{-- ========== MOBILE TOOLBAR (md:hidden) ========== --}}
<div class="flex flex-col gap-2 mb-4 md:hidden">
    {{-- Row 1: Search full-width --}}
    @if($showSearch)
        <form wire:submit="searchData">
            <label class="input input-bordered input-sm flex items-center gap-2 w-full transition-all duration-200 focus-within:input-primary focus-within:shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-4 w-4 text-base-content/40">
                    <path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd"/>
                </svg>
                <input type="text" class="grow text-sm" placeholder="{{ mrcatz_lang('search_placeholder') }}" wire:model="search"
                       @if($typeSearchWithDelay)
                           x-data @input.debounce.{{$typeSearchDelay}}="$dispatch('search-typing', { value: $event.target.value })"
                       @elseif($typeSearch)
                           x-data @input="$dispatch('search-typing', { value: $event.target.value })"
                       @endif/>
            </label>
        </form>
    @endif

    {{-- Row 2: Actions --}}
    <div class="flex items-center gap-2.5 flex-wrap">
        @if(count($filters) > 0)
            <label class="btn btn-square btn-primary swap swap-rotate tooltip tooltip-bottom min-w-[44px] min-h-[44px]" data-tip="Filter">
                <input type="checkbox" x-on:change="open = ! open"/>
                {!! mrcatz_icon('tune', 'swap-off text-xl') !!}
                {!! mrcatz_icon('close', 'swap-on text-xl') !!}
            </label>
        @endif

        @if(count($filters) > 0 || $showSearch)
            <button class="btn btn-ghost btn-square border border-base-content/15 tooltip tooltip-bottom min-w-[44px] min-h-[44px]" data-tip="{{ mrcatz_lang('filter_preset') }}"
                    @click="document.getElementById('modal-mobile-preset')?.showModal()">
                {!! mrcatz_icon('bookmarks', 'text-xl') !!}
            </button>
        @endif

        @if($enableColumnVisibility)
            <button class="btn btn-ghost btn-square border border-base-content/15 tooltip tooltip-bottom min-w-[44px] min-h-[44px]" data-tip="{{ mrcatz_lang('col_visibility') }}"
                    @click="document.getElementById('modal-mobile-columns')?.showModal()">
                {!! mrcatz_icon('view_column', 'text-xl') !!}
            </button>
        @endif

        @if(count($filters) > 0 || $showSearch)
            <button class="btn btn-ghost btn-square border border-base-content/15 tooltip tooltip-bottom min-w-[44px] min-h-[44px]" data-tip="{{ mrcatz_lang('btn_reset') }}"
                    x-on:click="
                        if ($wire.search || $wire.activeFilters.filter(f => f.value != null).length > 0) {
                            document.getElementById('modal-reset-confirm')?.showModal()
                        } else {
                            $wire.resetData()
                        }
                    ">
                {!! mrcatz_icon('restart_alt', 'text-xl') !!}
            </button>
        @endif

        {{-- Spacer --}}
        <div class="flex-1"></div>

        @if($showExportButton)
            <button class="btn btn-ghost border border-base-content/15 btn-square tooltip tooltip-bottom min-w-[44px] min-h-[44px]" data-tip="{{ mrcatz_lang('btn_export') }}" wire:click="openExportModal">
                {!! mrcatz_icon('download', 'text-xl') !!}
            </button>
        @endif
        @if($bulkEnabled && $showBulkButton)
            <button class="btn btn-square tooltip tooltip-bottom min-w-[44px] min-h-[44px] {{ $bulkActive ? 'btn-secondary' : 'btn-ghost border border-base-content/15' }}"
                    data-tip="{{ mrcatz_lang('btn_select') }}" wire:click="toggleBulk">
                {!! mrcatz_icon($bulkActive ? 'check_box' : 'check_box_outline_blank', 'text-xl') !!}
            </button>
        @endif
    </div>

    @if($showAddButton)
        <div class="flex justify-end">
            <button class="btn btn-primary gap-2 shadow-sm min-h-[44px]" wire:click="addData()">
                {!! mrcatz_icon('add', 'text-xl') !!}
                <span>{{ mrcatz_lang('btn_add') }}</span>
            </button>
        </div>
    @endif

    {{-- Row 3: Active filter count (only if filters active) --}}
    @if(count($filters) > 0 && $activeFilterCount > 0)
        <div class="flex items-center gap-2 px-2.5 py-1.5 rounded-xl bg-base-200/40 w-fit">
            {!! mrcatz_icon('filter_alt', 'text-sm text-base-content/40') !!}
            <p class="text-xs text-base-content/60">
                <span class="font-semibold text-base-content">{{ $activeFilterCount }}</span> {{ mrcatz_lang('filter_active') }}
            </p>
        </div>
    @endif
</div>

{{-- ========== DESKTOP TOOLBAR (hidden md:flex) ========== --}}
<div class="hidden md:flex md:items-center md:justify-between gap-3 mb-4">
    <div class="flex items-center gap-2">
        @if($showSearch)
            <form wire:submit="searchData">
                <label class="input input-bordered input-md flex items-center gap-2 w-72 transition-all duration-200 focus-within:input-primary focus-within:shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16" fill="currentColor" class="h-4 w-4 text-base-content/40">
                        <path fill-rule="evenodd" d="M9.965 11.026a5 5 0 1 1 1.06-1.06l2.755 2.754a.75.75 0 1 1-1.06 1.06l-2.755-2.754ZM10.5 7a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0Z" clip-rule="evenodd"/>
                    </svg>
                    <input type="text" class="grow text-sm" placeholder="{{ mrcatz_lang('search_placeholder') }}" wire:model="search"
                           @if($typeSearchWithDelay)
                               x-data @input.debounce.{{$typeSearchDelay}}="$dispatch('search-typing', { value: $event.target.value })"
                           @elseif($typeSearch)
                               x-data @input="$dispatch('search-typing', { value: $event.target.value })"
                           @endif/>
                </label>
            </form>
        @endif

        @if(count($filters) > 0)
            <label class="btn btn-md btn-square btn-primary swap swap-rotate tooltip tooltip-bottom" data-tip="Filter">
                <input type="checkbox" x-on:change="open = ! open"/>
                {!! mrcatz_icon('tune', 'swap-off text-lg') !!}
                {!! mrcatz_icon('close', 'swap-on text-lg') !!}
            </label>
            @if($activeFilterCount > 0)
                <div class="flex items-center gap-2 px-3 py-1.5 rounded-xl bg-base-200/40">
                    {!! mrcatz_icon('filter_alt', 'text-sm text-base-content/40') !!}
                    <p class="text-sm text-base-content/60">
                        <span class="font-semibold text-base-content">{{ $activeFilterCount }}</span> {{ mrcatz_lang('filter_active') }}
                    </p>
                </div>
            @endif
        @endif

        @if(count($filters) > 0 || $showSearch)
            <div class="relative">
                <button class="btn btn-md btn-ghost btn-square border border-base-content/15 tooltip tooltip-bottom" data-tip="{{ mrcatz_lang('filter_preset') }}"
                        @click="presetOpen = !presetOpen">
                    {!! mrcatz_icon('bookmarks', 'text-lg') !!}
                </button>
                <div x-show="presetOpen" @click.outside="presetOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-full mt-2 w-64 bg-base-100 border border-base-content/10 rounded-xl shadow-xl z-50 p-3 space-y-2">
                    @include('mrcatz::components.ui.partials.preset-content')
                </div>
            </div>
        @endif
    </div>

    <div class="flex items-center gap-2">
        @if($showAddButton)
            <button class="btn btn-md btn-primary gap-2 shadow-sm tooltip tooltip-bottom" data-tip="{{ mrcatz_lang('btn_add') }}" wire:click="addData()">
                {!! mrcatz_icon('add', 'text-lg') !!}
                <span>{{ mrcatz_lang('btn_add') }}</span>
            </button>
        @endif
        @if($bulkEnabled && $showBulkButton)
            <button class="btn btn-md gap-1 tooltip tooltip-bottom {{ $bulkActive ? 'btn-secondary' : 'btn-ghost border border-base-content/15' }}"
                    data-tip="{{ mrcatz_lang('btn_select') }}" wire:click="toggleBulk">
                {!! mrcatz_icon($bulkActive ? 'check_box' : 'check_box_outline_blank', 'text-lg') !!}
                <span class="text-sm">{{ mrcatz_lang('btn_select') }}</span>
            </button>
        @endif
        @if($showExportButton)
            <button class="btn btn-md btn-ghost border border-base-content/15 gap-1 tooltip tooltip-bottom"
                    data-tip="{{ mrcatz_lang('btn_export') }}" wire:click="openExportModal">
                {!! mrcatz_icon('download', 'text-lg') !!}
                <span class="text-sm">{{ mrcatz_lang('btn_export') }}</span>
            </button>
        @endif
        @if($enableColumnVisibility)
            <div class="relative">
                <button class="btn btn-md btn-ghost btn-square border border-base-content/15 tooltip tooltip-bottom" data-tip="{{ mrcatz_lang('col_visibility') }}"
                        @click="colVisOpen = !colVisOpen">
                    {!! mrcatz_icon('view_column', 'text-lg') !!}
                </button>
                <div x-show="colVisOpen" @click.outside="colVisOpen = false"
                     x-transition:enter="transition ease-out duration-150"
                     x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-100"
                     x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                     class="absolute right-0 top-full mt-2 w-56 bg-base-100 border border-base-content/10 rounded-xl shadow-xl z-50 p-3 space-y-1">
                    <p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide mb-2">{{ mrcatz_lang('col_visibility') }}</p>
                    @foreach(range(0, $totalCols - 1) as $ci)
                        <label class="flex items-center gap-2 px-2 py-1.5 rounded-lg hover:bg-base-200/50 cursor-pointer">
                            <input type="checkbox" class="checkbox checkbox-xs checkbox-primary"
                                   @checked(!in_array($ci, $hiddenColumns))
                                   wire:click="toggleColumn({{ $ci }})"/>
                            <span class="text-sm text-base-content/70">{{ $posts->getHead($ci) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>
        @endif
        @if(count($filters) > 0 || $showSearch)
            <button class="btn btn-md btn-ghost btn-square border border-base-content/15 tooltip tooltip-bottom" data-tip="{{ mrcatz_lang('btn_reset') }}"
                    x-on:click="
                        if ($wire.search || $wire.activeFilters.filter(f => f.value != null).length > 0) {
                            document.getElementById('modal-reset-confirm')?.showModal()
                        } else {
                            $wire.resetData()
                        }
                    ">
                {!! mrcatz_icon('restart_alt', 'text-lg') !!}
            </button>
        @endif
    </div>
</div>

@include('mrcatz::components.ui.datatable-filter')

@if($bulkShow && count($selectedRows) > 0)
    <div class="mb-4 px-3 py-2 md:px-4 md:py-2.5 rounded-xl bg-primary/5 border border-primary/20 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div class="flex items-center gap-2">
            {!! mrcatz_icon('check_circle', 'text-sm text-primary') !!}
            <span class="text-sm font-medium text-primary">{{ count($selectedRows) }} {{ mrcatz_lang('data_selected') }}</span>
        </div>
        <div class="flex gap-2">
            <button class="btn btn-xs btn-error btn-outline gap-1 flex-1 sm:flex-none"
                    x-on:click="document.getElementById('modal-bulk-delete')?.showModal()">
                {!! mrcatz_icon('delete', 'text-xs') !!}
                {{ mrcatz_lang('btn_delete') }}
            </button>
            <button class="btn btn-xs btn-ghost gap-1 flex-1 sm:flex-none" wire:click="clearSelection">
                {!! mrcatz_icon('close', 'text-xs') !!}
                {{ mrcatz_lang('btn_cancel') }}
            </button>
        </div>
    </div>
@endif
