{{-- Reusable inline edit cell --}}
{{-- @props: $cellId, $value, $ref, $inputClass, $variant ('desktop'|'mobile-header'|'mobile-pill') --}}
@php $isDesktop = $variant === 'desktop'; @endphp
<{{ $isDesktop ? 'div' : 'div' }} x-data="{
        editing: false,
        saving: false,
        val: '{{ e($value) }}',
        error: '',
        submit() {
            this.editing = false;
            this.error = '';
            this.saving = true;
            $wire.inlineUpdate({!! $rowDataJson !!}, '{{ $columnKey }}', this.val, {{ $rowIndex }});
        }
    }"
    x-on:inline-validation-error.window="if ($event.detail.cellId === '{{ $cellId }}') { saving = false; error = $event.detail.error; editing = true; $nextTick(() => $refs['{{ $ref }}']?.focus()) }"
    x-on:inline-save-done.window="if ($event.detail.cellId === '{{ $cellId }}') { saving = false }"
    @if($isDesktop)
    @dblclick.stop="editing = true; error = ''; $nextTick(() => $refs['{{ $ref }}']?.focus())"
    @click.stop="if (window.innerWidth < 768 && !editing) { editing = true; error = ''; $nextTick(() => $refs['{{ $ref }}']?.focus()) }"
    @endif
    style="{{ !$isDesktop ? 'display:contents' : '' }}">

    @if($isDesktop)
        <span x-show="!editing && !saving" class="group/edit inline-flex items-center gap-1.5 cursor-text px-2 py-0.5 -mx-2 rounded bg-primary/5 hover:bg-primary/10 border border-dashed border-primary/20 hover:border-primary/40 transition-all duration-150">
            <span>{!! $display !!}</span>
            <svg class="w-3 h-3 text-primary/30 group-hover/edit:text-primary/60 shrink-0 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg>
        </span>
        <span x-show="saving" x-cloak class="inline-flex items-center gap-1.5 px-2 py-0.5 -mx-2 text-base-content/40">
            <span class="loading loading-spinner loading-xs"></span>
        </span>
    @elseif($variant === 'mobile-header')
        <span class="text-[10px] text-base-content/30 uppercase tracking-wider font-semibold flex items-center gap-1">{{ $head }} <svg class="w-2.5 h-2.5 text-primary/40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg></span>
        <p x-show="!editing && !saving" @click.stop="editing = true; error = ''; $nextTick(() => $refs['{{ $ref }}']?.focus())"
           class="text-sm font-semibold text-base-content truncate cursor-text rounded bg-primary/5 border border-dashed border-primary/20 px-1.5 py-0.5 {{ $uppercaseClass }}">{!! $display !!}</p>
        <span x-show="saving" x-cloak class="inline-flex items-center gap-1.5 py-0.5 text-base-content/40">
            <span class="loading loading-spinner loading-xs"></span>
        </span>
    @elseif($variant === 'mobile-pill')
        <span class="text-[11px] text-base-content/40 block mb-0.5 flex items-center gap-1">{{ $head }} <svg class="w-2.5 h-2.5 text-primary/40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg></span>
        <span x-show="!editing && !saving" @click.stop="editing = true; error = ''; $nextTick(() => $refs['{{ $ref }}']?.focus())"
              class="text-sm text-base-content/80 cursor-text block truncate rounded bg-primary/5 border border-dashed border-primary/20 px-1.5 py-0.5 {{ $uppercaseClass }}">{!! $display !!}</span>
        <span x-show="saving" x-cloak class="inline-flex items-center gap-1.5 py-0.5 text-base-content/40">
            <span class="loading loading-spinner loading-xs"></span>
        </span>
    @endif

    <div x-show="editing" class="{{ $isDesktop ? 'inline-flex' : 'flex' }} flex-col {{ !$isDesktop ? 'mt-0.5' : '' }}" @click.stop>
        <input x-ref="{{ $ref }}" x-model="val"
               @keydown.enter.prevent="submit()"
               @keydown.escape.prevent="editing = false; error = ''"
               @blur="if (!error) { editing = false }"
               class="input {{ $inputSize }} input-bordered w-full {{ $isDesktop ? 'max-w-[200px]' : '' }} text-sm {{ $variant === 'mobile-header' ? 'font-semibold' : '' }}"
               :class="error ? 'input-error' : ''"/>
        <span x-show="error" x-text="error" role="alert" aria-live="assertive" class="text-error text-xs mt-0.5 {{ $isDesktop ? 'max-w-[200px]' : '' }}"></span>
    </div>
</div>
