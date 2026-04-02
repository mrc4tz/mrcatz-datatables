{{-- Shared preset content — used in both desktop dropdown and mobile bottom-sheet --}}
<p class="text-xs font-semibold text-base-content/50 uppercase tracking-wide">{{ mrcatz_lang('filter_preset') }}</p>
<template x-if="presets.length === 0">
    <p class="text-xs text-base-content/30 italic py-2">{{ mrcatz_lang('filter_no_preset') }}</p>
</template>
<template x-for="(p, i) in presets" :key="i">
    <div class="flex items-center justify-between gap-2 px-2 py-1.5 rounded-lg hover:bg-base-200/50 cursor-pointer group">
        <button class="text-sm text-base-content/70 truncate flex-1 text-left" @click="loadPreset(p)" x-text="p.name"></button>
        <button class="text-xs text-base-content/20 group-hover:text-error transition-colors" @click.stop="deletePreset(i)">{!! mrcatz_icon('close', 'text-xs') !!}</button>
    </div>
</template>
<div class="border-t border-base-content/10 pt-2 mt-2">
    <div class="flex gap-1">
        <input type="text" class="input input-bordered input-xs flex-1 text-xs" placeholder="{{ mrcatz_lang('filter_preset_placeholder') }}"
               x-model="presetName" @keydown.enter.prevent="savePreset()"/>
        <button class="btn btn-xs btn-primary btn-square" @click="savePreset()">
            {!! mrcatz_icon('save', 'text-xs') !!}
        </button>
    </div>
</div>
