import './bootstrap';
import Sortable from 'sortablejs';

let dragging = false;

function initSortables() {
    if (dragging) {
        return;
    }

    document.querySelectorAll('[data-sortable]').forEach((el) => {
        if (el.dataset.sortableReady === '1' && el._sortable) {
            return;
        }

        if (el._sortable) {
            el._sortable.destroy();
        }

        el._sortable = Sortable.create(el, {
            handle: '.drag-handle',
            animation: 150,
            ghostClass: 'sortable-ghost',
            chosenClass: 'sortable-chosen',
            delay: 120,
            delayOnTouchOnly: true,
            touchStartThreshold: 4,
            onStart() {
                dragging = true;
            },
            onEnd() {
                dragging = false;

                const order = [...el.querySelectorAll(':scope > [data-id]')].map((item) => item.dataset.id);
                const method = el.dataset.sortableMethod || 'reorder';
                const componentEl = el.closest('[wire\\:id]');
                const componentId = componentEl?.getAttribute('wire:id');

                if (componentId && window.Livewire) {
                    window.Livewire.find(componentId).call(method, order);
                }
            },
        });

        el.dataset.sortableReady = '1';
    });
}

function resetSortableFlags() {
    document.querySelectorAll('[data-sortable]').forEach((el) => {
        delete el.dataset.sortableReady;
    });
}

document.addEventListener('DOMContentLoaded', initSortables);
document.addEventListener('livewire:navigated', () => {
    resetSortableFlags();
    initSortables();
});

document.addEventListener('livewire:init', () => {
    initSortables();

    Livewire.hook('morph.updated', () => {
        resetSortableFlags();
        queueMicrotask(initSortables);
    });
});
