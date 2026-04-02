{{-- Reusable inline edit cell (desktop only) --}}
{{-- @props: $cellId, $value, $ref, $columnKey, $rowIndex, $rowDataJson, $display --}}
<div x-data="{
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
    @dblclick.stop="editing = true; error = ''; $nextTick(() => $refs['{{ $ref }}']?.focus())"
    @click.stop="if (window.innerWidth < 768 && !editing) { editing = true; error = ''; $nextTick(() => $refs['{{ $ref }}']?.focus()) }">

    <span x-show="!editing && !saving" class="group/edit inline-flex items-center gap-1.5 cursor-text px-2 py-0.5 -mx-2 rounded bg-primary/5 hover:bg-primary/10 border border-dashed border-primary/20 hover:border-primary/40 transition-all duration-150">
        <span>{!! $display !!}</span>
        <svg class="w-3 h-3 text-primary/30 group-hover/edit:text-primary/60 shrink-0 transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"><path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z"/></svg>
    </span>
    <span x-show="saving" x-cloak class="inline-flex items-center gap-1.5 px-2 py-0.5 -mx-2 text-base-content/40">
        <span class="loading loading-spinner loading-xs"></span>
    </span>

    <div x-show="editing" class="inline-flex flex-col" @click.stop>
        <input x-ref="{{ $ref }}" x-model="val"
               @keydown.enter.prevent="submit()"
               @keydown.escape.prevent="editing = false; error = ''"
               @blur="if (!error) { editing = false }"
               class="input input-xs input-bordered w-full max-w-[200px] text-sm"
               :class="error ? 'input-error' : ''"/>
        <span x-show="error" x-text="error" role="alert" aria-live="assertive" class="text-error text-xs mt-0.5 max-w-[200px]"></span>
    </div>
</div>
