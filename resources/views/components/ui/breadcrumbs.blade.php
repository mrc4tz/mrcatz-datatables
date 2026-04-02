{{-- MrCatz DataTable Breadcrumbs (opsional) --}}
@if(!empty($breadcrumbs))
    <div class="breadcrumbs text-sm mb-6">
        <ul>
            @foreach($breadcrumbs as $bread)
                @if($bread['url'] != null)
                    <li>
                        <a href="{{$bread['url']}}" class="flex items-center gap-1 font-semibold text-primary hover:text-primary/80 transition-colors">
                            @if($loop->first)
                                {!! mrcatz_icon('home', 'text-sm') !!}
                            @endif
                            {{ $bread['title'] }}
                        </a>
                    </li>
                @else
                    <li>
                        <span class="text-base-content/50">{{ $bread['title'] }}</span>
                    </li>
                @endif
            @endforeach
        </ul>
    </div>
@endif
