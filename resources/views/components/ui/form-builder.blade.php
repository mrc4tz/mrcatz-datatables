{{-- MrCatz Form Builder — auto-generated form from setForm() --}}
@php
    $formFields = $this->getFormFields();
@endphp

<div class="grid grid-cols-12 gap-4">
    @foreach($formFields as $field)
        @if(!$this->shouldShowField($field))
            @continue
        @endif

        @php
            $type = $field['type'];
            $id = $field['id'];
            $span = $field['span'] ?? 12;
            $disabled = $field['disabled'] ?? false;
            $wireDirective = $field['wireDirective'] ?? '';
            $onChangeAttr = $field['onChange'] ? 'wire:change=formFieldChanged(\'' . $id . '\',$event.target.value)' : '';
        @endphp

        <div class="col-span-12 sm:col-span-{{ $span }}">

            {{-- ═══ HIDDEN ═══ --}}
            @if($type === 'hidden')
                <input type="hidden" {{ $wireDirective }} />

            {{-- ═══ SECTION HEADER ═══ --}}
            @elseif($type === 'section')
                <h2 class="text-lg font-semibold mt-4 mb-1 pb-2 border-b border-base-content/10 text-base-content">
                    {{ $field['content'] }}
                </h2>

            {{-- ═══ NOTE ═══ --}}
            @elseif($type === 'note')
                <p class="text-sm text-base-content/60 mb-1">{{ $field['content'] }}</p>

            {{-- ═══ ALERT ═══ --}}
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

            {{-- ═══ RAW HTML ═══ --}}
            @elseif($type === 'html')
                {!! $field['content'] !!}

            {{-- ═══ TEXT / EMAIL / PASSWORD ═══ --}}
            @elseif(in_array($type, ['text', 'email', 'password']))
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <label class="input input-bordered flex items-center gap-3 w-full transition-all duration-200
                        focus-within:input-primary focus-within:shadow-sm
                        @error($id) input-error @enderror
                        @if($disabled) opacity-60 bg-base-200 @endif">
                        @if($field['icon'])
                            <span class="text-base-content/40 text-lg shrink-0">{!! mrcatz_form_icon($field['icon'], 'text-base-content/40 text-lg') !!}</span>
                        @endif
                        @if($field['prefix'])
                            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['prefix'] }}</span>
                        @endif
                        <input type="{{ $type }}"
                               class="grow text-sm min-w-0"
                               placeholder="{{ $field['placeholder'] ?? '...' }}"
                               {!! $wireDirective !!}
                               {!! $onChangeAttr !!}
                               @if($disabled) disabled @endif />
                        @if($field['suffix'])
                            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['suffix'] }}</span>
                        @endif
                    </label>
                    @error($id)
                        <p class="text-error text-xs mt-1 flex items-center gap-1">
                            {!! mrcatz_icon('error', 'text-xs') !!}
                            {{ $message }}
                        </p>
                    @enderror
                    @if($field['hint'])
                        <p class="text-base-content/50 text-xs mt-1">{{ $field['hint'] }}</p>
                    @endif
                </fieldset>

            {{-- ═══ NUMBER ═══ --}}
            @elseif($type === 'number')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <label class="input input-bordered flex items-center gap-3 w-full transition-all duration-200
                        focus-within:input-primary focus-within:shadow-sm
                        @error($id) input-error @enderror
                        @if($disabled) opacity-60 bg-base-200 @endif">
                        @if($field['icon'])
                            <span class="text-base-content/40 text-lg shrink-0">{!! mrcatz_form_icon($field['icon'], 'text-base-content/40 text-lg') !!}</span>
                        @endif
                        @if($field['prefix'])
                            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['prefix'] }}</span>
                        @endif
                        <input type="number"
                               class="grow text-sm min-w-0"
                               placeholder="{{ $field['placeholder'] ?? '...' }}"
                               {!! $wireDirective !!}
                               {!! $onChangeAttr !!}
                               @if($field['step']) step="{{ $field['step'] }}" @endif
                               @if($field['min'] !== null) min="{{ $field['min'] }}" @endif
                               @if($field['max'] !== null) max="{{ $field['max'] }}" @endif
                               @if($disabled) disabled @endif />
                        @if($field['suffix'])
                            <span class="text-base-content/50 text-sm font-medium shrink-0">{{ $field['suffix'] }}</span>
                        @endif
                    </label>
                    @error($id)
                        <p class="text-error text-xs mt-1 flex items-center gap-1">
                            {!! mrcatz_icon('error', 'text-xs') !!}
                            {{ $message }}
                        </p>
                    @enderror
                    @if($field['hint'])
                        <p class="text-base-content/50 text-xs mt-1">{{ $field['hint'] }}</p>
                    @endif
                </fieldset>

            {{-- ═══ SELECT ═══ --}}
            @elseif($type === 'select')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <select class="select select-bordered w-full text-sm transition-all duration-200
                        focus:select-primary focus:shadow-sm
                        @error($id) select-error @enderror
                        @if($disabled) opacity-60 bg-base-200 @endif"
                            {!! $wireDirective !!}
                            {!! $onChangeAttr !!}
                            @if($disabled) disabled @endif>
                        <option value="">-- {{ $field['label'] }} --</option>
                        @foreach(($field['data'] ?? []) as $d)
                            @php
                                $optVal = is_array($d) ? ($d[$field['valueKey']] ?? '') : (is_object($d) ? ($d->{$field['valueKey']} ?? '') : '');
                                $optLabel = is_array($d) ? ($d[$field['optionKey']] ?? '') : (is_object($d) ? ($d->{$field['optionKey']} ?? '') : '');
                            @endphp
                            <option value="{{ $optVal }}">{{ $optLabel }}</option>
                        @endforeach
                    </select>
                    @error($id)
                        <p class="text-error text-xs mt-1 flex items-center gap-1">
                            {!! mrcatz_icon('error', 'text-xs') !!}
                            {{ $message }}
                        </p>
                    @enderror
                    @if($field['hint'])
                        <p class="text-base-content/50 text-xs mt-1">{{ $field['hint'] }}</p>
                    @endif
                </fieldset>

            {{-- ═══ TEXTAREA ═══ --}}
            @elseif($type === 'textarea')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <textarea class="textarea w-full textarea-bordered h-28 text-sm transition-all duration-200
                        focus:textarea-primary focus:shadow-sm
                        @error($id) textarea-error @enderror
                        @if($disabled) opacity-60 bg-base-200 @endif"
                              placeholder="{{ $field['placeholder'] ?? '...' }}"
                              {!! $wireDirective !!}
                              {!! $onChangeAttr !!}
                              @if($disabled) disabled @endif></textarea>
                    @error($id)
                        <p class="text-error text-xs mt-1 flex items-center gap-1">
                            {!! mrcatz_icon('error', 'text-xs') !!}
                            {{ $message }}
                        </p>
                    @enderror
                    @if($field['hint'])
                        <p class="text-base-content/50 text-xs mt-1">{{ $field['hint'] }}</p>
                    @endif
                </fieldset>

            {{-- ═══ FILE ═══ --}}
            @elseif($type === 'file')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    @if($field['preview'])
                        <div class="mb-2">
                            @if(preg_match('/\.(jpg|jpeg|png|gif|webp|svg)$/i', $field['preview']))
                                <img src="{{ $field['preview'] }}" alt="Preview" class="max-h-32 rounded-lg border border-base-content/10 object-cover" />
                            @else
                                <a href="{{ $field['preview'] }}" target="_blank" class="link link-primary text-sm flex items-center gap-1">
                                    {!! mrcatz_icon('download', 'text-sm') !!}
                                    {{ basename($field['preview']) }}
                                </a>
                            @endif
                        </div>
                    @endif
                    <input type="file"
                           class="file-input file-input-bordered w-full
                               @error($id) file-input-error @enderror
                               @if($disabled) opacity-60 bg-base-200 @endif"
                           {!! $wireDirective !!}
                           @if($field['accept']) accept="{{ $field['accept'] }}" @endif
                           @if($disabled) disabled @endif />
                    @error($id)
                        <p class="text-error text-xs mt-1 flex items-center gap-1">
                            {!! mrcatz_icon('error', 'text-xs') !!}
                            {{ $message }}
                        </p>
                    @enderror
                    @if($field['hint'])
                        <p class="text-base-content/50 text-xs mt-1">{{ $field['hint'] }}</p>
                    @endif
                </fieldset>

            {{-- ═══ TOGGLE ═══ --}}
            @elseif($type === 'toggle')
                <fieldset class="fieldset">
                    <label class="label cursor-pointer justify-start gap-3 p-3 rounded-lg border border-base-content/10 hover:bg-base-200/50 transition-colors duration-200
                        @if($disabled) opacity-60 bg-base-200 @endif">
                        <input type="checkbox"
                               class="toggle toggle-primary toggle-sm"
                               {!! $wireDirective !!}
                               {!! $onChangeAttr !!}
                               @if($disabled) disabled @endif />
                        <span class="text-base-content text-sm font-medium">{{ $field['label'] }}</span>
                    </label>
                    @if($field['hint'])
                        <p class="text-base-content/50 text-xs mt-1">{{ $field['hint'] }}</p>
                    @endif
                </fieldset>

            {{-- ═══ CHOOSER ═══ --}}
            @elseif($type === 'chooser')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <div class="p-4 border border-base-content/15 rounded-lg bg-base-200/30 @error($id) border-error @enderror">
                        <div class="flex flex-wrap gap-2">
                            @foreach(($field['data'] ?? []) as $d)
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
                                       @if($disabled) disabled @endif />
                            @endforeach
                        </div>
                    </div>
                    @error($id)
                        <p class="text-error text-xs mt-1 flex items-center gap-1">
                            {!! mrcatz_icon('error', 'text-xs') !!}
                            {{ $message }}
                        </p>
                    @enderror
                    @if($field['hint'])
                        <p class="text-base-content/50 text-xs mt-1">{{ $field['hint'] }}</p>
                    @endif
                </fieldset>

            {{-- ═══ RADIO ═══ --}}
            @elseif($type === 'radio')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <div class="flex flex-wrap gap-4 p-3 border border-base-content/10 rounded-lg
                        @if($disabled) opacity-60 bg-base-200 @endif">
                        @foreach(($field['options'] ?? []) as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio"
                                       name="radio_{{ $id }}"
                                       class="radio radio-sm radio-primary"
                                       value="{{ $val }}"
                                       {!! $wireDirective !!}
                                       {!! $onChangeAttr !!}
                                       @if($disabled) disabled @endif />
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
                    @if($field['hint'])
                        <p class="text-base-content/50 text-xs mt-1">{{ $field['hint'] }}</p>
                    @endif
                </fieldset>

            @endif
        </div>
    @endforeach
</div>
