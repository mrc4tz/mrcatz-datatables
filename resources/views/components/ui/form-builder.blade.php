{{-- MrCatz Form Builder — auto-generated form from setForm() --}}
@php
    $formFields = $this->getFormFields();

    // Helper: build DaisyUI style class for a component prefix (input, select, textarea, etc.)
    // e.g. buildStyleClass('input', $field) => 'input-primary input-lg'
    if (!function_exists('mrcatz_fb_classes')) {
        function mrcatz_fb_classes(string $component, array $field): string {
            $classes = '';
            if (!empty($field['style'])) {
                $classes .= $component . '-' . $field['style'] . ' ';
            }
            if (!empty($field['size'])) {
                $classes .= $component . '-' . $field['size'] . ' ';
            }
            return trim($classes);
        }
    }

    // Full static class map — Tailwind JIT needs complete class strings in source.
    // No responsive prefix: form is inside a modal that's already responsive.
    // col-span-1 col-span-2 col-span-3 col-span-4 col-span-5 col-span-6
    // col-span-7 col-span-8 col-span-9 col-span-10 col-span-11 col-span-12
    $spanClassMap = [
        1  => 'col-span-1',
        2  => 'col-span-2',
        3  => 'col-span-3',
        4  => 'col-span-4',
        5  => 'col-span-5',
        6  => 'col-span-6',
        7  => 'col-span-7',
        8  => 'col-span-8',
        9  => 'col-span-9',
        10 => 'col-span-10',
        11 => 'col-span-11',
        12 => 'col-span-12',
    ];
@endphp

<style>
    @media (max-width: 640px) {
        .mrcatz-form-grid > div {
            grid-column: 1 / -1 !important;
            grid-row: auto !important;
            order: var(--mrcatz-mobile-order, 0);
        }
    }
</style>
@php
    $formGap = $this->formGap ?? '1rem';
    $formColumnGap = $this->formColumnGap ?? '1.5rem';
@endphp
<div class="mrcatz-form-grid grid grid-cols-12" style="row-gap: {{ $formGap }}; column-gap: {{ $formColumnGap }}">
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

            $inlineStyles = collect([
                $rowSpan ? "grid-row: 1 / span {$rowSpan}" : null,
                $mobileOrder !== null ? "--mrcatz-mobile-order: {$mobileOrder}" : null,
            ])->filter()->implode('; ');
        @endphp

        <div class="{{ $spanClass }} @if(!$show) hidden @endif" wire:key="mrcatz-fb-{{ $fieldIndex }}" @if($inlineStyles) style="{{ $inlineStyles }}" @endif>
        @if($show)

            {{-- ═══ HIDDEN ═══ --}}
            @if($type === 'hidden')
                <input type="hidden" {!! $wireDirective !!} />

            {{-- ═══ SECTION HEADER ═══ --}}
            @elseif($type === 'section')
                <h2 class="text-lg font-semibold mt-4 mb-1 pb-2 border-b border-base-content/10 text-base-content">
                    {{ $field['content'] }}
                </h2>

            {{-- ═══ NOTE ═══ --}}
            @elseif($type === 'note')
                <p class="text-sm text-base-content/60 mb-1">{{ $field['content'] }}</p>

            {{-- ═══ DIVIDER ═══ --}}
            @elseif($type === 'divider')
                @if($field['content'])
                    <div class="divider text-sm text-base-content/50">{{ $field['content'] }}</div>
                @else
                    <div class="divider"></div>
                @endif

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

            {{-- ═══ BUTTON ═══ --}}
            @elseif($type === 'button')
                @php
                    $btnStyle = $field['buttonStyle'] ?? 'primary';
                    $btnSizeClass = !empty($field['size']) ? 'btn-' . $field['size'] : '';
                    $btnClass = 'btn btn-' . $btnStyle . ' ' . $btnSizeClass;
                @endphp
                <fieldset class="fieldset">
                    {{-- Empty legend to align with adjacent input fields --}}
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">&nbsp;</legend>
                    <button type="button"
                            class="{{ trim($btnClass) }} gap-2 w-full"
                            wire:click="{{ $field['onClick'] }}"
                            @if($field['loading'] && $field['target'])
                                wire:loading.attr="disabled" wire:target="{{ $field['target'] }}"
                            @elseif($field['loading'])
                                wire:loading.attr="disabled" wire:target="{{ $field['onClick'] }}"
                            @endif
                            @if($disabled) disabled @endif>
                        @if($field['loading'])
                            <span class="loading loading-spinner loading-xs"
                                  @if($field['target'])
                                      wire:loading wire:target="{{ $field['target'] }}"
                                  @else
                                      wire:loading wire:target="{{ $field['onClick'] }}"
                                  @endif></span>
                        @endif
                        @if($field['icon'])
                            {!! mrcatz_form_icon($field['icon'], 'text-lg') !!}
                        @endif
                        {{ $field['label'] }}
                    </button>
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
                </fieldset>

            {{-- ═══ TEXT / EMAIL / PASSWORD / URL / TEL / SEARCH / DATE / TIME / DATETIME-LOCAL ═══ --}}
            @elseif(in_array($type, ['text', 'email', 'password', 'url', 'tel', 'search', 'date', 'time', 'datetime-local']))
                @php $sc = mrcatz_fb_classes('input', $field); @endphp
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <label class="input input-bordered {{ $sc }} flex items-center gap-3 w-full transition-all duration-200
                        focus-within:shadow-sm
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
                </fieldset>

            {{-- ═══ NUMBER ═══ --}}
            @elseif($type === 'number')
                @php $sc = mrcatz_fb_classes('input', $field); @endphp
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <label class="input input-bordered {{ $sc }} flex items-center gap-3 w-full transition-all duration-200
                        focus-within:shadow-sm
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
                </fieldset>

            {{-- ═══ SELECT ═══ --}}
            @elseif($type === 'select')
                @php $sc = mrcatz_fb_classes('select', $field); @endphp
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <select class="select select-bordered {{ $sc }} w-full text-sm transition-all duration-200
                        focus:shadow-sm
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
                </fieldset>

            {{-- ═══ TEXTAREA ═══ --}}
            @elseif($type === 'textarea')
                @php $sc = mrcatz_fb_classes('textarea', $field); @endphp
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <textarea class="textarea w-full textarea-bordered {{ $sc }} h-28 text-sm transition-all duration-200
                        focus:shadow-sm
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
                </fieldset>

            {{-- ═══ IMAGE ═══ --}}
            @elseif($type === 'image')
                @php
                    $sc = mrcatz_fb_classes('file-input', $field);
                    $modalId = 'modal_delete_' . $id;
                    $lightboxId = 'lightbox_' . $id;
                    $isUploadMode = !empty($field['onUpload']);

                    $pvClass = $field['previewClass'] ?? 'rounded-full ring ring-primary ring-offset-base-100 ring-offset-2';
                    $pw = $field['previewWidth'] ?? 128;
                    $ph = $field['previewHeight'] ?? 128;
                @endphp
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <div class="flex flex-col items-center gap-4 @if($isUploadMode) p-4 border border-base-content/10 rounded-lg bg-base-200/20 @endif">
                        {{-- Preview: clickable for lightbox --}}
                        <div class="shrink-0 overflow-hidden {{ $field['preview'] ? 'cursor-pointer transition-opacity hover:opacity-80' : '' }} {{ $pvClass }}"
                             style="width: {{ $pw }}px; height: {{ $ph }}px;"
                             @if($field['preview']) onclick="document.getElementById('{{ $lightboxId }}').showModal()" @endif>
                            @if($field['preview'])
                                <img src="{{ $field['preview'] }}" alt="{{ $field['label'] }}"
                                     style="width: 100%; height: 100%; object-fit: cover; object-position: center; display: block;" />
                            @else
                                <div class="w-full h-full flex items-center justify-center {{ $field['fallback'] ? 'bg-primary/10' : 'bg-base-300' }}">
                                    @if($field['fallback'])
                                        <span class="text-4xl font-bold text-primary">{{ strtoupper(substr($field['fallback'], 0, 1)) }}</span>
                                    @else
                                        {!! mrcatz_form_icon('person', 'text-base-content/30 w-12 h-12') !!}
                                    @endif
                                </div>
                            @endif
                        </div>

                        {{-- Upload UI (only if onUpload is set) --}}
                        @if($isUploadMode)
                            <div class="w-full max-w-xs">
                                <input type="file"
                                       class="file-input file-input-bordered file-input-sm {{ $sc }} w-full
                                           @error($id) file-input-error @enderror"
                                       {!! $wireDirective !!}
                                       @if($field['accept']) accept="{{ $field['accept'] }}" @endif
                                       @if($disabled) disabled @endif />
                                @error($id)
                                    <p class="text-error text-xs mt-1 flex items-center gap-1">
                                        {!! mrcatz_icon('error', 'text-xs') !!}
                                        {{ $message }}
                                    </p>
                                @enderror

                                <div class="flex gap-2 mt-3">
                                    <button type="button"
                                            class="btn btn-primary btn-sm flex-1 gap-1"
                                            wire:click="{{ $field['onUpload'] }}"
                                            wire:loading.attr="disabled"
                                            wire:target="{{ $field['onUpload'] }},{{ $id }}">
                                        <span class="loading loading-spinner loading-xs"
                                              wire:loading wire:target="{{ $field['onUpload'] }},{{ $id }}"></span>
                                        Upload
                                    </button>
                                    @if($field['onDelete'] && $field['preview'])
                                        <button type="button"
                                                class="btn btn-error btn-sm btn-outline gap-1"
                                                onclick="document.getElementById('{{ $modalId }}').showModal()">
                                            {!! mrcatz_icon('delete', 'text-sm') !!}
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($field['hint'])
                            @php
                                $hintCls = match($field['hintColor'] ?? null) {
                                    'success' => 'text-success',
                                    'error'   => 'text-error',
                                    'warning' => 'text-warning',
                                    'info'    => 'text-info',
                                    default   => 'text-base-content/40',
                                };
                            @endphp
                            <p class="{{ $hintCls }} text-xs">{{ $field['hint'] }}</p>
                        @endif
                    </div>
                </fieldset>

                {{-- Delete confirmation modal --}}
                @if($field['onDelete'] && $field['preview'])
                    <dialog id="{{ $modalId }}" class="modal modal-bottom sm:modal-middle">
                        <div class="modal-box bg-base-100 rounded-t-2xl sm:rounded-2xl shadow-2xl">
                            <div class="flex justify-center mb-4">
                                <div class="w-16 h-16 rounded-full bg-error/10 flex items-center justify-center">
                                    {!! mrcatz_icon('warning', 'text-3xl text-error') !!}
                                </div>
                            </div>
                            <h3 class="text-lg font-bold text-center text-base-content">
                                {{ $field['deleteConfirm'] ?? 'Hapus foto ini?' }}
                            </h3>
                            <p class="py-4 text-center text-base-content/60 text-sm">
                                Foto yang dihapus tidak dapat dikembalikan.
                            </p>
                            <div class="modal-action justify-center gap-3">
                                <button type="button"
                                        class="btn btn-error gap-2 px-6 shadow-sm"
                                        wire:click="{{ $field['onDelete'] }}"
                                        onclick="document.getElementById('{{ $modalId }}').close()">
                                    {!! mrcatz_icon('delete_forever', 'text-lg') !!}
                                    Hapus
                                </button>
                                <form method="dialog">
                                    <button class="btn btn-ghost">Batal</button>
                                </form>
                            </div>
                        </div>
                        <form method="dialog" class="modal-backdrop"><button>close</button></form>
                    </dialog>
                @endif

                {{-- Lightbox: transparent backdrop, scroll zoom --}}
                @if($field['preview'])
                    <dialog id="{{ $lightboxId }}" class="modal bg-black/80 backdrop-blur-sm"
                            x-data="{ scale: 1 }"
                            @close="scale = 1"
                            @wheel.prevent="scale = Math.min(5, Math.max(0.25, scale + ($event.deltaY < 0 ? 0.15 : -0.15)))"
                            onclick="if(event.target===this)this.close()">
                        <div class="flex flex-col items-center justify-center w-full h-full p-4" onclick="if(event.target===this)this.closest('dialog').close()">
                            {{-- Controls --}}
                            <div class="flex items-center gap-2 mb-3">
                                <button type="button" class="btn btn-sm btn-circle bg-white/10 border-0 text-white hover:bg-white/20" @click="scale = Math.max(0.25, scale - 0.25)">-</button>
                                <span class="text-white/80 text-xs w-12 text-center" x-text="Math.round(scale * 100) + '%'"></span>
                                <button type="button" class="btn btn-sm btn-circle bg-white/10 border-0 text-white hover:bg-white/20" @click="scale = Math.min(5, scale + 0.25)">+</button>
                                <button type="button" class="btn btn-sm btn-circle bg-white/10 border-0 text-white hover:bg-white/20" @click="scale = 1">
                                    {!! mrcatz_icon('restart_alt', 'text-xs') !!}
                                </button>
                                <button type="button" class="btn btn-sm btn-circle bg-white/10 border-0 text-white hover:bg-white/20 ml-2" onclick="this.closest('dialog').close()">
                                    {!! mrcatz_icon('close', 'text-xs') !!}
                                </button>
                            </div>
                            {{-- Image --}}
                            <img src="{{ $field['preview'] }}" alt="{{ $field['label'] }}"
                                 class="max-h-[85vh] max-w-[90vw] rounded-lg shadow-2xl transition-transform duration-150 origin-center select-none"
                                 draggable="false"
                                 :style="'transform: scale(' + scale + ')'" />
                        </div>
                    </dialog>
                @endif

            {{-- ═══ FILE ═══ --}}
            @elseif($type === 'file')
                @php $sc = mrcatz_fb_classes('file-input', $field); @endphp
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
                           class="file-input file-input-bordered {{ $sc }} w-full
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
                </fieldset>

            {{-- ═══ TOGGLE ═══ --}}
            @elseif($type === 'toggle')
                @php $sc = mrcatz_fb_classes('toggle', $field); @endphp
                <fieldset class="fieldset">
                    <label class="label cursor-pointer justify-start gap-3 p-3 rounded-lg border border-base-content/10 hover:bg-base-200/50 transition-colors duration-200
                        @if($disabled) opacity-60 bg-base-200 @endif">
                        <input type="checkbox"
                               class="toggle toggle-primary {{ $sc }}"
                               {!! $wireDirective !!}
                               {!! $onChangeAttr !!}
                               @if($disabled) disabled @endif />
                        <span class="text-base-content text-sm font-medium">{{ $field['label'] }}</span>
                    </label>
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
                </fieldset>

            {{-- ═══ CHECKBOX (single) ═══ --}}
            @elseif($type === 'checkbox')
                @php $sc = mrcatz_fb_classes('checkbox', $field); @endphp
                <fieldset class="fieldset">
                    <label class="label cursor-pointer justify-start gap-3 p-3 rounded-lg border border-base-content/10 hover:bg-base-200/50 transition-colors duration-200
                        @if($disabled) opacity-60 bg-base-200 @endif">
                        <input type="checkbox"
                               class="checkbox checkbox-primary {{ $sc }}"
                               {!! $wireDirective !!}
                               {!! $onChangeAttr !!}
                               @if($disabled) disabled @endif />
                        <span class="text-base-content text-sm font-medium">{{ $field['label'] }}</span>
                    </label>
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
                </fieldset>

            {{-- ═══ RADIO ═══ --}}
            @elseif($type === 'radio')
                @php $sc = mrcatz_fb_classes('radio', $field); @endphp
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <div class="flex flex-wrap gap-4 p-3 border border-base-content/10 rounded-lg
                        @if($disabled) opacity-60 bg-base-200 @endif">
                        @foreach(($field['options'] ?? []) as $val => $label)
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="radio"
                                       name="radio_{{ $id }}"
                                       class="radio radio-primary {{ $sc }}"
                                       value="{{ $val }}"
                                       {!! $wireDirective !!}
                                       {!! $onChangeAttr !!}
                                       @if($disabled) disabled @endif />
                                <span class="text-sm">{{ $label }}</span>
                            </label>
                        @endforeach
                    </div>
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
                </fieldset>

            {{-- ═══ COLOR ═══ --}}
            @elseif($type === 'color')
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <input type="color"
                           class="w-16 h-10 rounded-lg border border-base-content/15 cursor-pointer
                               @if($disabled) opacity-60 @endif"
                           {!! $wireDirective !!}
                           {!! $onChangeAttr !!}
                           @if($disabled) disabled @endif />
                    @error($id)
                        <p class="text-error text-xs mt-1 flex items-center gap-1">
                            {!! mrcatz_icon('error', 'text-xs') !!}
                            {{ $message }}
                        </p>
                    @enderror
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
                </fieldset>

            {{-- ═══ RANGE / SLIDER ═══ --}}
            @elseif($type === 'range')
                @php $sc = mrcatz_fb_classes('range', $field); @endphp
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <input type="range"
                           class="range range-primary {{ $sc }} w-full
                               @if($disabled) opacity-60 @endif"
                           {!! $wireDirective !!}
                           {!! $onChangeAttr !!}
                           @if($field['min'] !== null) min="{{ $field['min'] }}" @endif
                           @if($field['max'] !== null) max="{{ $field['max'] }}" @endif
                           @if($field['step']) step="{{ $field['step'] }}" @endif
                           @if($disabled) disabled @endif />
                    <div class="flex justify-between text-xs text-base-content/50 px-1">
                        <span>{{ $field['min'] ?? 0 }}</span>
                        <span>{{ $field['max'] ?? 100 }}</span>
                    </div>
                    @error($id)
                        <p class="text-error text-xs mt-1 flex items-center gap-1">
                            {!! mrcatz_icon('error', 'text-xs') !!}
                            {{ $message }}
                        </p>
                    @enderror
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
                </fieldset>

            {{-- ═══ RATING ═══ --}}
            @elseif($type === 'rating')
                @php $sc = mrcatz_fb_classes('rating', $field); @endphp
                <fieldset class="fieldset">
                    <legend class="fieldset-legend text-xs font-semibold text-base-content/70 uppercase tracking-wide">{{ $field['label'] }}</legend>
                    <div class="rating {{ $sc }} @if($disabled) opacity-60 @endif">
                        @for($i = 1; $i <= ($field['max'] ?? 5); $i++)
                            <input type="radio"
                                   name="rating_{{ $id }}"
                                   class="mask mask-star-2 bg-warning"
                                   value="{{ $i }}"
                                   {!! $wireDirective !!}
                                   @if($disabled) disabled @endif />
                        @endfor
                    </div>
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
                </fieldset>

            @endif
        @endif
        </div>
    @endforeach
</div>
