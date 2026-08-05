<template>
    <div ref="root" class="relative inline-block text-left">
        <button
            type="button"
            class="w-11 h-11 sm:w-8 sm:h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-gray-200 active:bg-gray-200 transition-colors"
            title="Más opciones"
            aria-haspopup="menu"
            :aria-expanded="open"
            @click.stop="open = !open"
        >
            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                <circle cx="12" cy="5" r="1.7" />
                <circle cx="12" cy="12" r="1.7" />
                <circle cx="12" cy="19" r="1.7" />
            </svg>
        </button>

        <div
            v-if="open"
            role="menu"
            class="absolute right-0 top-full mt-1 z-40 min-w-[11rem] rounded-lg bg-[#26262b] border border-[#3a3a42] shadow-xl py-1"
            @click="open = false"
        >
            <slot />
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const open = ref(false);
const root = ref(null);

function onDocClick(e) {
    if (!root.value?.contains(e.target)) open.value = false;
}
function onKey(e) {
    if (e.key === 'Escape') open.value = false;
}

onMounted(() => {
    document.addEventListener('click', onDocClick);
    document.addEventListener('keydown', onKey);
});
onBeforeUnmount(() => {
    document.removeEventListener('click', onDocClick);
    document.removeEventListener('keydown', onKey);
});
</script>
