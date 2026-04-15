{{-- Multi-checkbox buttons --}}
@php
    $chooserData = $field['data'] ?? [];
    $chooserSearchable = count($chooserData) > 10;
    $chooserId = 'mrcatz-chooser-' . $id;
@endphp
<fieldset class="fieldset">
    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
    <div class="p-4 border border-base-content/15 rounded-lg bg-base-200/30 @error($errorKey ?? $id) border-error @enderror"
         @if($chooserSearchable)
         x-data="{ search: '' }"
         @endif
    >
        @if($chooserSearchable)
        <input type="text" x-model="search"
               placeholder="{{ mrcatz_lang('chooser_search_placeholder') }}"
               class="input input-sm input-bordered w-full mb-3 bg-base-100" />
        @endif
        <div class="flex flex-wrap gap-2">
            @foreach($chooserData as $d)
                @php
                    $optVal = is_array($d) ? ($d[$field['valueKey']] ?? '') : (is_object($d) ? ($d->{$field['valueKey']} ?? '') : '');
                    $optLabel = is_array($d) ? ($d[$field['optionKey']] ?? '') : (is_object($d) ? ($d->{$field['optionKey']} ?? '') : '');
                @endphp
                <input class="btn btn-sm transition-all duration-200"
                       type="checkbox"
                       value="{{ $optVal }}"
                       {!! $wireDirective !!}
                       name="options"
                       aria-label="{{ $optLabel }}"
                       @if($chooserSearchable)
                       x-show="!search || {{ \Js::from(strtolower($optLabel)) }}.includes(search.toLowerCase())"
                       @endif
                       @if($disabled) disabled @endif />
            @endforeach
        </div>
    </div>
    @include('mrcatz::components.ui.partials.form._error')
    @include('mrcatz::components.ui.partials.form._hint')
</fieldset>
