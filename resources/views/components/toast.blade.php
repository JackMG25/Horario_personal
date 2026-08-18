<div
    x-data="{
        show: false,
        message: @js(session('notify')),
        init() {
            if (this.message) {
                this.show = true;
                setTimeout(() => this.show = false, 2200);
            }

            Livewire.on('notify', (event) => {
                this.message = event[0]?.message ?? event.message ?? event;
                this.show = true;
                setTimeout(() => this.show = false, 2200);
            });
        }
    }"
    x-show="show"
    x-transition.opacity.duration.200ms
    x-cloak
    class="pointer-events-none fixed inset-x-0 top-4 z-50 flex justify-center px-4"
>
    <div class="pointer-events-auto rounded-full bg-slate-900 px-4 py-2 text-sm font-medium text-white shadow-lg">
        <span x-text="message"></span>
    </div>
</div>
