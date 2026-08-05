<template>
    <AppLayout title="Configuración de congregación">
        <div class="max-w-lg">
            <form @submit.prevent="form.put('/congregation/settings')" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre de la congregación *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Ciudad</label>
                    <input
                        v-model="form.city"
                        type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Ciudad (opcional)"
                    />
                    <p v-if="form.errors.city" class="mt-1 text-xs text-red-600">{{ form.errors.city }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cargos de acomodadores *</label>
                    <p class="text-xs text-gray-400 mb-2">
                        Estos cargos se usan al crear nuevos programas. Los programas ya creados no cambian.
                    </p>
                    <div class="space-y-2">
                        <div
                            v-for="(role, i) in form.attendant_roles"
                            :key="i"
                            class="flex items-center gap-2"
                        >
                            <input
                                v-model="form.attendant_roles[i]"
                                type="text"
                                class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                                placeholder="Ej. Micrófono 1"
                            />
                            <button
                                type="button"
                                :disabled="form.attendant_roles.length <= 1"
                                class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 disabled:opacity-30 disabled:hover:bg-transparent disabled:hover:text-gray-400 transition-colors shrink-0"
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
                        class="mt-2 inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800"
                        @click="form.attendant_roles.push('')"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                        </svg>
                        Agregar cargo
                    </button>
                    <p v-if="rolesError" class="mt-1 text-xs text-red-600">{{ rolesError }}</p>
                </div>

                <div class="pt-2">
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg disabled:opacity-50"
                    >
                        Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    congregation:   { type: Object, required: true },
    attendantRoles: { type: Array,  default: () => [] },
});

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
</script>
