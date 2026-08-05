<template>
    <AppLayout title="Configuración de congregación">
        <div class="max-w-4xl flex flex-col md:flex-row gap-6 md:gap-8">

            <!-- ── Sidebar: categories ──────────────────────────────────── -->
            <nav class="md:w-52 shrink-0">
                <ul class="flex md:flex-col gap-1 overflow-x-auto md:overflow-visible">
                    <li v-for="section in sections" :key="section.key">
                        <button
                            type="button"
                            :class="[
                                'w-full text-left px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors whitespace-nowrap',
                                activeSection === section.key
                                    ? 'bg-indigo-50 text-indigo-700'
                                    : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50',
                            ]"
                            @click="activeSection = section.key"
                        >
                            {{ section.label }}
                        </button>
                    </li>
                </ul>
            </nav>

            <!-- ── Content ──────────────────────────────────────────────── -->
            <form @submit.prevent="form.put('/congregation/settings')" class="flex-1 min-w-0 space-y-6">

                <!-- General -->
                <div v-show="activeSection === 'general'" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Información general</h2>
                        <p class="text-xs text-gray-400 mt-0.5">Nombre y ubicación de la congregación.</p>
                    </div>

                    <div class="px-5 sm:px-6 py-5 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">
                                Nombre de la congregación <span class="text-red-400">*</span>
                            </label>
                            <input
                                v-model="form.name"
                                type="text"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                :class="{ 'border-red-400 focus:ring-red-400': form.errors.name }"
                            />
                            <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Ciudad</label>
                            <input
                                v-model="form.city"
                                type="text"
                                class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                                placeholder="Ciudad (opcional)"
                            />
                            <p v-if="form.errors.city" class="mt-1 text-xs text-red-600">{{ form.errors.city }}</p>
                        </div>
                    </div>
                </div>

                <!-- Cargos -->
                <div v-show="activeSection === 'cargos'" class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="px-5 sm:px-6 py-4 border-b border-gray-100">
                        <h2 class="text-sm font-semibold text-gray-900">Cargos de acomodadores</h2>
                        <p class="text-xs text-gray-400 mt-0.5">
                            Arrastra para ordenar. Este orden se usa al crear programas y en el PDF.
                            Los programas ya creados no cambian.
                        </p>
                    </div>

                    <div class="px-5 sm:px-6 py-5">
                        <div class="space-y-2">
                            <div
                                v-for="(role, i) in form.attendant_roles"
                                :key="i"
                                :draggable="dragEnabled === i"
                                :class="[
                                    'flex items-center gap-2 rounded-lg border px-2 py-1.5 transition-colors bg-white',
                                    dragIndex === i
                                        ? 'border-indigo-300 ring-2 ring-indigo-100 shadow-md opacity-90'
                                        : 'border-gray-100 hover:border-gray-200',
                                ]"
                                @dragstart="onDragStart(i, $event)"
                                @dragover.prevent="onDragOver(i)"
                                @dragend="onDragEnd"
                                @drop.prevent
                            >
                                <!-- Drag handle -->
                                <button
                                    type="button"
                                    class="w-8 h-8 flex items-center justify-center rounded-md text-gray-300 hover:text-gray-500 hover:bg-gray-50 cursor-grab active:cursor-grabbing shrink-0 touch-none"
                                    title="Arrastrar para ordenar"
                                    @mousedown="dragEnabled = i"
                                    @mouseup="dragEnabled = null"
                                >
                                    <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                        <circle cx="9" cy="6" r="1.5" /><circle cx="15" cy="6" r="1.5" />
                                        <circle cx="9" cy="12" r="1.5" /><circle cx="15" cy="12" r="1.5" />
                                        <circle cx="9" cy="18" r="1.5" /><circle cx="15" cy="18" r="1.5" />
                                    </svg>
                                </button>

                                <!-- Order badge -->
                                <span class="w-6 h-6 rounded-full bg-indigo-50 text-indigo-600 text-xs font-bold flex items-center justify-center shrink-0">
                                    {{ i + 1 }}
                                </span>

                                <input
                                    v-model="form.attendant_roles[i]"
                                    type="text"
                                    class="flex-1 min-w-0 border-0 bg-transparent px-2 py-1.5 text-sm text-gray-900 focus:outline-none focus:ring-0 placeholder-gray-300"
                                    placeholder="Ej. Micrófono 1"
                                />

                                <button
                                    type="button"
                                    :disabled="form.attendant_roles.length <= 1"
                                    class="w-8 h-8 flex items-center justify-center rounded-md text-gray-300 hover:text-red-500 hover:bg-red-50 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-gray-300 transition-colors shrink-0"
                                    title="Quitar cargo"
                                    @click="form.attendant_roles.splice(i, 1)"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="mt-3 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors"
                            @click="form.attendant_roles.push('')"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                            </svg>
                            Agregar cargo
                        </button>
                        <p v-if="rolesError" class="mt-1 text-xs text-red-600">{{ rolesError }}</p>
                    </div>
                </div>

                <!-- ── Save bar ─────────────────────────────────────────── -->
                <div class="flex items-center justify-end gap-3">
                    <Transition
                        enter-active-class="transition duration-150"
                        enter-from-class="opacity-0"
                        leave-active-class="transition duration-300"
                        leave-to-class="opacity-0"
                    >
                        <span v-if="form.recentlySuccessful" class="text-sm text-green-600 font-medium">
                            Guardado ✓
                        </span>
                    </Transition>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg disabled:opacity-50 transition-colors"
                    >
                        {{ form.processing ? 'Guardando…' : 'Guardar cambios' }}
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    congregation:   { type: Object, required: true },
    attendantRoles: { type: Array,  default: () => [] },
});

const sections = [
    { key: 'general', label: 'General' },
    { key: 'cargos',  label: 'Cargos de acomodadores' },
];
const activeSection = ref('general');

const form = useForm({
    name: props.congregation.name,
    city: props.congregation.city ?? '',
    attendant_roles: props.attendantRoles.length ? [...props.attendantRoles] : [''],
});

const rolesError = computed(() =>
    form.errors.attendant_roles
    ?? Object.entries(form.errors).find(([k]) => k.startsWith('attendant_roles.'))?.[1]
    ?? null
);

// ── Drag & drop ordering (native HTML5 DnD, handle-activated) ───────────────
const dragIndex   = ref(null); // row currently being dragged
const dragEnabled = ref(null); // row whose handle is pressed (draggable=true)

function onDragStart(i, e) {
    dragIndex.value = i;
    e.dataTransfer.effectAllowed = 'move';
    e.dataTransfer.setData('text/plain', ''); // Firefox needs data to start a drag
}

function onDragOver(i) {
    if (dragIndex.value === null || dragIndex.value === i) return;
    const list = form.attendant_roles;
    const [moved] = list.splice(dragIndex.value, 1);
    list.splice(i, 0, moved);
    dragIndex.value = i;
}

function onDragEnd() {
    dragIndex.value = null;
    dragEnabled.value = null;
}
</script>
