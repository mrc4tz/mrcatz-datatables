{{-- MrCatz Form Builder — auto-generated form from setForm() --}}
@php
    $formFields = $this->getFormFields();

    if (!function_exists('mrcatz_fb_classes')) {
        function mrcatz_fb_classes(string $component, array $field): string {
            $classes = '';
            if (!empty($field['style'])) $classes .= $component . '-' . $field['style'] . ' ';
            if (!empty($field['size']))  $classes .= $component . '-' . $field['size'] . ' ';
            return trim($classes);
        }
    }

    // col-span-1 col-span-2 col-span-3 col-span-4 col-span-5 col-span-6
    // col-span-7 col-span-8 col-span-9 col-span-10 col-span-11 col-span-12
    $spanClassMap = [
        1 => 'col-span-1',  2 => 'col-span-2',  3 => 'col-span-3',  4 => 'col-span-4',
        5 => 'col-span-5',  6 => 'col-span-6',  7 => 'col-span-7',  8 => 'col-span-8',
        9 => 'col-span-9', 10 => 'col-span-10', 11 => 'col-span-11', 12 => 'col-span-12',
    ];
@endphp

<style>
    @media (max-width: 640px) {
        .mrcatz-form-grid > div {
            grid-column: 1 / -1 !important;
            grid-row: auto !important;
            padding-left: 0 !important;
            order: var(--mrcatz-mobile-order, 0);
        }
    }
</style>

@php
    $formGap = $this->formGap ?? '1rem';
    $formColumnGap = $this->formColumnGap ?? '1.5rem';
@endphp
<div class="mrcatz-form-grid grid grid-cols-12" style="gap: {{ $formGap }}">
    @foreach($formFields as $fieldIndex => $field)
        @php
            $show = $this->shouldShowField($field);
            $type = $field['type'];
            $id = $field['id'];
            $span = $field['span'] ?? 12;
            $disabled = $field['disabled'] ?? false;
            $wireDirective = $field['wireDirective'] ?? '';
            $onChangeAttr = ($field['onChange'] ?? null) ? 'wire:change=formFieldChanged(\'' . $id . '\',$event.target.value)' : '';
            $spanClass = $spanClassMap[$span] ?? 'col-span-12';
            $rowSpan = $field['rowSpan'] ?? null;
            $mobileOrder = $field['mobileOrder'] ?? null;
            $marginClass = $field['margin'] ?? '';
            $paddingClass = $field['padding'] ?? '';

            $inlineStyles = collect([
                $rowSpan ? "grid-row: 1 / span {$rowSpan}" : null,
                $rowSpan ? "padding-left: {$formColumnGap}" : null,
                $mobileOrder !== null ? "--mrcatz-mobile-order: {$mobileOrder}" : null,
            ])->filter()->implode('; ');
        @endphp

        <div class="{{ $spanClass }} {{ $marginClass }} {{ $paddingClass }} @if(!$show) hidden @endif" wire:key="mrcatz-fb-{{ $fieldIndex }}" @if($inlineStyles) style="{{ $inlineStyles }}" @endif>
        @if($show)
            @if(in_array($type, ['hidden', 'section', 'note', 'divider', 'alert', 'html']))
                @include('mrcatz::components.ui.partials.form.static')
            @elseif($type === 'button')
                @include('mrcatz::components.ui.partials.form.button')
            @elseif(in_array($type, ['text', 'email', 'password', 'url', 'tel', 'search', 'date', 'time', 'datetime-local']))
                @include('mrcatz::components.ui.partials.form.input')
            @elseif($type === 'number')
                @include('mrcatz::components.ui.partials.form.number')
            @elseif($type === 'select')
                @include('mrcatz::components.ui.partials.form.select')
            @elseif($type === 'textarea')
                @include('mrcatz::components.ui.partials.form.textarea')
            @elseif($type === 'image')
                @include('mrcatz::components.ui.partials.form.image')
            @elseif($type === 'file')
                @include('mrcatz::components.ui.partials.form.file')
            @elseif($type === 'fileupload')
                @include('mrcatz::components.ui.partials.form.fileupload')
            @elseif($type === 'toggle')
                @include('mrcatz::components.ui.partials.form.toggle')
            @elseif($type === 'checkbox')
                @include('mrcatz::components.ui.partials.form.checkbox')
            @elseif($type === 'chooser')
                @include('mrcatz::components.ui.partials.form.chooser')
            @elseif($type === 'radio')
                @include('mrcatz::components.ui.partials.form.radio')
            @elseif($type === 'color')
                @include('mrcatz::components.ui.partials.form.color')
            @elseif($type === 'range')
                @include('mrcatz::components.ui.partials.form.range')
            @elseif($type === 'rating')
                @include('mrcatz::components.ui.partials.form.rating')
            @endif
        @endif
        </div>
    @endforeach
</div>
