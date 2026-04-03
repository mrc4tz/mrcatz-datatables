{{-- MrCatz Global Lightbox — include once in layout, reused by all images --}}
<style>
    #mrcatz-lightbox { background:none;border:none;outline:none;padding:0;max-width:100vw;max-height:100vh;width:100vw;height:100vh; }
    #mrcatz-lightbox::backdrop { background:rgba(0,0,0,0.85);backdrop-filter:blur(4px); }
    #mrcatz-lightbox[open] { animation:mrcatz-lb-in 200ms ease-out; }
    @keyframes mrcatz-lb-in { from{opacity:0;transform:scale(0.95)} to{opacity:1;transform:scale(1)} }
</style>
<dialog id="mrcatz-lightbox"
        x-data="{ scale: 1, justReset: false }"
        x-on:mrcatz-lightbox.window="$el.querySelector('img').src = $event.detail.url; scale = 1; justReset = false; $el.showModal()"
        @close="scale = 1; justReset = false"
        @wheel.prevent="scale = Math.min(5, Math.max(0.25, scale + ($event.deltaY < 0 ? 0.15 : -0.15)))">
    <div class="flex items-center justify-center w-full h-full cursor-default"
         @click.self="if(scale !== 1) { scale = 1; justReset = true } else { $el.closest('dialog').close() }">
        <img src="" alt=""
             class="max-h-[85vh] max-w-[90vw] rounded-lg shadow-2xl transition-transform duration-200 origin-center select-none cursor-default"
             draggable="false"
             :style="'transform: scale(' + scale + ')'"
             @click.stop="if(justReset) { justReset = false; return } if(scale !== 1) { scale = 1; justReset = true } else { $el.closest('dialog').close() }" />
    </div>
</dialog>
