@if($paginator->hasData)
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        {{-- Rows per page --}}
        <div class="flex items-center gap-3">
            <label hidden>
                <input type="text" wire:model="p">
                <input type="text" wire:model="key">
                <input type="text" wire:model="value">
                <input type="text" wire:model="dataTableSet">
            </label>
            <span class="text-xs text-base-content/50">{{ mrcatz_lang('rows_per_page') }}</span>
            <select class="select select-bordered select-xs focus:select-primary transition-all duration-200"
                    wire:change="paginate($event.target.value)">
                @foreach($paginator->paginateOptions as $i => $opt)
                    <option value="{{ $opt }}" @selected($paginator->perPage() == $opt)>{{ $opt }}</option>
                @endforeach
            </select>
        </div>

        @if ($paginator->hasPages())
            <div class="flex flex-col sm:flex-row items-center gap-3">
                {{-- Info --}}
                <span class="text-xs text-base-content/50">
                    {{ ($paginator->currentPage() * $paginator->perPage()) - ($paginator->perPage() - 1) }}
                    &ndash;
                    {{ ($paginator->currentPage() * $paginator->perPage()) - ($paginator->perPage() - 1) + (count($paginator->items()) - 1) }}
                    {{ mrcatz_lang('of') }}
                    {{ $paginator->total() }}
                </span>

                {{-- Mobile: simplified (prev, current/total, next) --}}
                <div class="join sm:hidden">
                    @if ($paginator->onFirstPage())
                        <button class="join-item btn btn-sm btn-disabled" disabled>
                            <span class="material-icons text-sm">chevron_left</span>
                        </button>
                    @else
                        <button class="join-item btn btn-sm hover:btn-primary transition-colors duration-200"
                                wire:click="previousPage('{{ $paginator->getPageName() }}')">
                            <span class="material-icons text-sm">chevron_left</span>
                        </button>
                    @endif

                    <button class="join-item btn btn-sm btn-primary text-xs font-bold">
                        {{ $paginator->currentPage() }} / {{ $paginator->lastPage() }}
                    </button>

                    @if ($paginator->hasMorePages())
                        <button class="join-item btn btn-sm hover:btn-primary transition-colors duration-200"
                                wire:click="nextPage('{{ $paginator->getPageName() }}')">
                            <span class="material-icons text-sm">chevron_right</span>
                        </button>
                    @else
                        <button class="join-item btn btn-sm btn-disabled" disabled>
                            <span class="material-icons text-sm">chevron_right</span>
                        </button>
                    @endif
                </div>

                {{-- Desktop: full page numbers --}}
                <div class="join hidden sm:flex">
                    @if ($paginator->onFirstPage())
                        <button class="join-item btn btn-sm btn-disabled" disabled>
                            <span class="material-icons text-sm">chevron_left</span>
                        </button>
                    @else
                        <button class="join-item btn btn-sm hover:btn-primary transition-colors duration-200"
                                wire:click="previousPage('{{ $paginator->getPageName() }}')">
                            <span class="material-icons text-sm">chevron_left</span>
                        </button>
                    @endif

                    @foreach ($paginator->onEachSide(1)->links()->elements as $element)
                        @if (is_string($element))
                            <button class="join-item btn btn-sm btn-disabled text-xs" disabled>{{ $element }}</button>
                        @endif
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <button class="join-item btn btn-sm btn-primary text-xs font-bold">{{ $page }}</button>
                                @else
                                    <button class="join-item btn btn-sm text-xs hover:btn-primary transition-colors duration-200"
                                            wire:click="goToP({{$page}},'{{ $paginator->getPageName() }}')">{{ $page }}</button>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    @if ($paginator->hasMorePages())
                        <button class="join-item btn btn-sm hover:btn-primary transition-colors duration-200"
                                wire:click="nextPage('{{ $paginator->getPageName() }}')">
                            <span class="material-icons text-sm">chevron_right</span>
                        </button>
                    @else
                        <button class="join-item btn btn-sm btn-disabled" disabled>
                            <span class="material-icons text-sm">chevron_right</span>
                        </button>
                    @endif
                </div>
            </div>
        @endif
    </div>
@endif
