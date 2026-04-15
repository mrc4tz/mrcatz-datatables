{{-- MrCatz Form Builder — auto-generated form from setForm().
     Accepts an optional `$formFields` include variable so callers can
     render a pre-built, differently-namespaced field set (e.g. the bulk
     action modal passes fields wired to `bulkFormData.*`). --}}
@php
    $formFields = isset($formFields) ? $formFields : $this->getFormFields();

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

    // Check if any section has cardBreak
    $hasCardBreak = collect($formFields)->contains(fn($f) => ($f['type'] ?? '') === 'section' && !empty($f['cardBreak']));

    // Group fields into card sections if cardBreak is used
    $cardGroups = [];
    if ($hasCardBreak) {
        $currentGroup = ['title' => null, 'fields' => []];
        foreach ($formFields as $field) {
            if (($field['type'] ?? '') === 'section' && !empty($field['cardBreak'])) {
                if (!empty($currentGroup['fields']) || $currentGroup['title'] !== null) {
                    $cardGroups[] = $currentGroup;
                }
                $currentGroup = ['title' => $field['content'], 'fields' => []];
            } else {
                $currentGroup['fields'][] = $field;
            }
        }
        if (!empty($currentGroup['fields']) || $currentGroup['title'] !== null) {
            $cardGroups[] = $currentGroup;
        }
    }
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

@if($hasCardBreak)
    {{-- Multi-card layout --}}
    @foreach($cardGroups as $groupIndex => $group)
        <div class="card bg-base-100 shadow-sm {{ !$loop->last ? 'mb-6' : '' }}">
            <div class="card-body">
                @if($group['title'])
                    <h2 class="card-title text-base flex items-center gap-2 text-base-content">
                        {{ $group['title'] }}
                    </h2>
                    <div class="divider mt-0 mb-2"></div>
                @endif

                <div class="mrcatz-form-grid grid grid-cols-12" style="gap: {{ $formGap }}">
                    @foreach($group['fields'] as $fieldIndex => $field)
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

                        <div class="{{ $spanClass }} {{ $marginClass }} {{ $paddingClass }} @if(!$show) hidden @endif" wire:key="mrcatz-fb-{{ $groupIndex }}-{{ $fieldIndex }}" @if($inlineStyles) style="{{ $inlineStyles }}" @endif>
                        @if($show)
                            @if(in_array($type, ['hidden', 'section', 'note', 'divider', 'alert', 'html']))
                                @include('mrcatz::components.ui.partials.form.static')
                            @elseif($type === 'button')
                                @include('mrcatz::components.ui.partials.form.button')
                            @elseif(in_array($type, ['text', 'email', 'password', 'url', 'tel', 'search', 'date', 'time', 'datetime-local', 'month']))
                                @include('mrcatz::components.ui.partials.form.input')
                            @elseif($type === 'number' || $type === 'year')
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
                            @elseif($type === 'map_picker')
                                @include('mrcatz::components.ui.partials.form.map-picker')
                            @elseif($type === 'editor')
                                @include('mrcatz::components.ui.partials.form.editor')
                            @elseif($type === 'editor_advance')
                                @include('mrcatz::components.ui.partials.form.editor_advance')
                            @elseif($type === 'taginput')
                                @include('mrcatz::components.ui.partials.form.taginput')
                            @endif
                        @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
@else
    {{-- Single grid layout (default) --}}
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
                @elseif(in_array($type, ['text', 'email', 'password', 'url', 'tel', 'search', 'date', 'time', 'datetime-local', 'month']))
                    @include('mrcatz::components.ui.partials.form.input')
                @elseif($type === 'number' || $type === 'year')
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
                @elseif($type === 'map_picker')
                    @include('mrcatz::components.ui.partials.form.map-picker')
                @elseif($type === 'editor')
                    @include('mrcatz::components.ui.partials.form.editor')
                @elseif($type === 'editor_advance')
                    @include('mrcatz::components.ui.partials.form.editor_advance')
                @elseif($type === 'taginput')
                    @include('mrcatz::components.ui.partials.form.taginput')
                @elseif($type === 'date_range')
                    @include('mrcatz::components.ui.partials.form.date-range')
                @endif
            @endif
            </div>
        @endforeach
    </div>
@endif
