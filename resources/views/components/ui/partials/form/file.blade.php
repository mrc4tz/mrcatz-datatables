{{-- File upload (basic) --}}
@php
    $sc = mrcatz_fb_classes('file-input', $field);
    $previewUrl = $field['preview'] ?? null;

    // Detect file type from URL/path for appropriate icon & styling
    $fileExt = $previewUrl ? strtolower(pathinfo(parse_url($previewUrl, PHP_URL_PATH) ?? $previewUrl, PATHINFO_EXTENSION)) : '';
    $isImage = in_array($fileExt, ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg']);
    $fileIcon = match(true) {
        $isImage => 'image',
        in_array($fileExt, ['pdf']) => 'picture_as_pdf',
        in_array($fileExt, ['xls', 'xlsx', 'csv']) => 'table_view',
        in_array($fileExt, ['doc', 'docx']) => 'edit_note',
        in_array($fileExt, ['zip', 'rar', '7z', 'tar', 'gz']) => 'save',
        default => 'download',
    };
    $fileBadge = match(true) {
        in_array($fileExt, ['pdf']) => 'badge-error',
        in_array($fileExt, ['xls', 'xlsx', 'csv']) => 'badge-success',
        in_array($fileExt, ['doc', 'docx']) => 'badge-info',
        in_array($fileExt, ['zip', 'rar', '7z', 'tar', 'gz']) => 'badge-warning',
        $isImage => 'badge-primary',
        default => 'badge-ghost',
    };
@endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    @if($previewUrl)
        <div class="mb-2">
            @if($isImage)
                <div class="shrink-0 overflow-hidden rounded-lg border border-base-content/10 cursor-zoom-in transition-opacity hover:opacity-80 inline-block"
                     style="max-height: 128px;"
                     x-data @click="$dispatch('mrcatz-lightbox', { url: '{{ $previewUrl }}' })">
                    <img src="{{ $previewUrl }}" alt="Preview" class="max-h-32 object-cover" />
                </div>
            @else
                <a href="{{ $previewUrl }}" target="_blank"
                   class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border border-base-content/10 bg-base-200/30 hover:bg-base-200/60 transition-colors text-sm">
                    {!! mrcatz_icon($fileIcon, 'text-lg') !!}
                    <div class="flex flex-col">
                        <span class="text-base-content/80 font-medium truncate max-w-48">{{ basename($previewUrl) }}</span>
                        <span class="badge {{ $fileBadge }} badge-xs uppercase mt-0.5">{{ $fileExt }}</span>
                    </div>
                </a>
            @endif
        </div>
    @endif
    <input type="file"
           class="file-input file-input-bordered {{ $sc }} w-full @error($id) file-input-error @enderror @if($disabled) opacity-60 bg-base-200 @endif"
           {!! $wireDirective !!}
           @if($field['accept']) accept="{{ $field['accept'] }}" @endif
           @if($disabled) disabled @endif />
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
