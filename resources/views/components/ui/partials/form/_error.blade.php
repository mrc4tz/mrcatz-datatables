@php $__err = $errorKey ?? $id; @endphp
@error($__err)
    <p class="text-error text-xs mt-1 flex items-center gap-1">
        {!! mrcatz_icon('error', 'text-xs') !!}
        {{ $message }}
    </p>
@enderror
