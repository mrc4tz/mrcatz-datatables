{{-- Static content elements: hidden, section, note, divider, alert, html --}}

@if($type === 'hidden')
    <input type="hidden" {!! $wireDirective !!} />

@elseif($type === 'section')
    <h2 class="text-lg font-semibold mt-4 mb-1 pb-2 border-b border-base-content/10 text-base-content">
        {{ $field['content'] }}
    </h2>

@elseif($type === 'note')
    <p class="text-sm text-base-content/60 mb-1">{{ $field['content'] }}</p>

@elseif($type === 'divider')
    @if($field['content'])
        <div class="divider text-sm text-base-content/50">{{ $field['content'] }}</div>
    @else
        <div class="divider"></div>
    @endif

@elseif($type === 'alert')
    @php
        $alertClass = match($field['alertType'] ?? 'info') {
            'warning' => 'alert-warning',
            'success' => 'alert-success',
            'error'   => 'alert-error',
            default   => 'alert-info',
        };
    @endphp
    <div class="alert {{ $alertClass }} text-sm">
        @if($field['alertType'] === 'warning')
            {!! mrcatz_icon('warning', 'shrink-0') !!}
        @elseif($field['alertType'] === 'error')
            {!! mrcatz_icon('error', 'shrink-0') !!}
        @elseif($field['alertType'] === 'success')
            {!! mrcatz_icon('check_circle', 'shrink-0') !!}
        @else
            {!! mrcatz_icon('info', 'shrink-0') !!}
        @endif
        <span>{{ $field['content'] }}</span>
    </div>

@elseif($type === 'html')
    {!! $field['content'] !!}
@endif
