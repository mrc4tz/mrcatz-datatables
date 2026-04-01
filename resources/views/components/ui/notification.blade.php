{{-- MrCatz DataTable Notification Toast --}}
<div x-data="noticesHandler()" class="fixed z-50 inset-0 flex flex-col-reverse items-end justify-start h-screen w-screen" @notice.window="add($event.detail)" style="pointer-events:none">
    <template x-for="notice of notices" :key="notice.id">
        <div role="alert" class="alert w-auto m-16 text-white"
             :class="{
                'alert-success': notice.type === 'success',
                'alert-info': notice.type === 'info',
                'alert-warning': notice.type === 'warning',
                'alert-error': notice.type === 'error',
             }"
             x-show="visible.includes(notice)"
             x-transition:enter="transition ease-in duration-200"
             x-transition:enter-start="transform opacity-0 translate-y-2"
             x-transition:enter-end="transform opacity-100"
             x-transition:leave="transition ease-out duration-500"
             x-transition:leave-start="transform translate-x-0 opacity-100"
             x-transition:leave-end="transform translate-x-full opacity-0"
             @click="remove(notice.id)" style="pointer-events:auto">
            <template x-if="notice.type === 'success'"><span class="material-icons">check_circle</span></template>
            <template x-if="notice.type === 'info'"><span class="material-icons">info</span></template>
            <template x-if="notice.type === 'warning'"><span class="material-icons">error</span></template>
            <template x-if="notice.type === 'error'"><span class="material-icons">cancel</span></template>
            <span x-text="notice.text"></span>
        </div>
    </template>
</div>

<script>
    function noticesHandler() {
        return {
            notices: [],
            visible: [],
            add(notice) {
                notice.id = Date.now();
                this.notices.push(notice);
                this.fire(notice.id);
            },
            fire(id) {
                this.visible.push(this.notices.find(notice => notice.id == id));
                const timeShown = 2000 * this.visible.length;
                setTimeout(() => { this.remove(id) }, timeShown);
            },
            remove(id) {
                const notice = this.visible.find(notice => notice.id == id);
                const index = this.visible.indexOf(notice);
                this.visible.splice(index, 1);
            },
        }
    }
</script>
