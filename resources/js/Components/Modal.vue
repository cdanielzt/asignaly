<template>
    <Teleport to="body">
        <Transition name="modal">
            <div
                v-if="show"
                class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-0 sm:p-4 bg-black/60"
                @click.self="$emit('close')"
            >
                <div class="modal-panel w-full max-w-lg rounded-t-2xl sm:rounded-2xl bg-[#1e1e1e] border-t sm:border border-[#333] shadow-2xl overflow-hidden flex flex-col max-h-[90dvh]">
                    <!-- Drag-handle affordance (mobile only) -->
                    <div class="sm:hidden mx-auto mt-2.5 h-1 w-9 rounded-full bg-[#3a3a3a] shrink-0"></div>

                    <div class="flex items-center justify-between px-4 sm:px-6 py-4 border-b border-[#2a2a2a] shrink-0">
                        <h3 class="text-base font-semibold text-white">{{ title }}</h3>
                        <button
                            type="button"
                            class="w-10 h-10 sm:w-8 sm:h-8 -mr-1 sm:mr-0 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-[#2a2a2a] transition-colors"
                            title="Cerrar"
                            @click="$emit('close')"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    <div class="px-4 sm:px-6 py-5 overflow-y-auto pb-safe">
                        <slot />
                    </div>
                </div>
            </div>
        </Transition>
    </Teleport>
</template>

<script setup>
import { watch, onBeforeUnmount } from 'vue';

const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
});
const emit = defineEmits(['close']);

function onKey(e) {
    if (e.key === 'Escape') emit('close');
}

watch(() => props.show, (open) => {
    document[open ? 'addEventListener' : 'removeEventListener']('keydown', onKey);
});

onBeforeUnmount(() => document.removeEventListener('keydown', onKey));
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
    transition: opacity 0.15s ease;
}
.modal-enter-from,
.modal-leave-to {
    opacity: 0;
}
/* Slide up from the bottom on mobile */
@media (max-width: 639px) {
    .modal-enter-active .modal-panel,
    .modal-leave-active .modal-panel {
        transition: transform 0.25s ease;
    }
    .modal-enter-from .modal-panel,
    .modal-leave-to .modal-panel {
        transform: translateY(100%);
    }
}
</style>
