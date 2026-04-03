@if($field['hint'])
    @php
        $hintCls = match($field['hintColor'] ?? null) {
            'success' => 'text-success',
            'error'   => 'text-error',
            'warning' => 'text-warning',
            'info'    => 'text-info',
            default   => 'text-base-content/50',
        };
    @endphp
    <p class="{{ $hintCls }} text-xs mt-1">{{ $field['hint'] }}</p>
@endif
