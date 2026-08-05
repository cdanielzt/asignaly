<template>
    <AppLayout title="Estudiantes">
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-gray-500">{{ students.length }} estudiante{{ students.length !== 1 ? 's' : '' }} en total</p>
            <button
                type="button"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors"
                @click="openCreate"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Agregar estudiante
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
            <table v-if="students.length" class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-6 py-3.5 font-semibold text-gray-600">Nombre</th>
                        <th class="text-left px-6 py-3.5 font-semibold text-gray-600">Género</th>
                        <th class="px-6 py-3.5 font-semibold text-gray-600 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="student in students" :key="student.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ student.name }}</td>
                        <td class="px-6 py-4">
                            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', genderBadge(student.gender)]">
                                {{ genderLabel(student.gender) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <RowActions>
                                <button type="button" class="menu-item" @click="openEdit(student)">Editar</button>
                                <div class="menu-divider"></div>
                                <button type="button" class="menu-item-danger" @click="confirmDelete(student)">Eliminar</button>
                            </RowActions>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-else class="py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.422L12 14z" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14L3.84 9.578" />
                </svg>
                <p class="text-gray-500 font-medium">Sin estudiantes aún</p>
                <p class="text-gray-400 text-xs mt-1">Agrega tu primer estudiante para comenzar.</p>
            </div>
        </div>

        <!-- Add / edit modal -->
        <Modal :show="formOpen" :title="editing ? 'Editar estudiante' : 'Agregar estudiante'" @close="closeForm">
            <form @submit.prevent="submitForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="ej. María Rodríguez"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-400"
                        :class="{ 'border-red-400 focus:ring-red-400': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Género</label>
                    <div class="flex flex-col gap-2">
                        <label
                            v-for="[value, label] in Object.entries(genders)"
                            :key="value"
                            :class="[
                                'flex items-center gap-3 border rounded-lg px-4 py-3 cursor-pointer transition-colors',
                                form.gender === value ? genderSelected(value) : 'border-gray-200 hover:border-gray-300',
                            ]"
                        >
                            <input type="radio" v-model="form.gender" :value="value" class="accent-indigo-600" />
                            <span class="text-sm font-medium text-gray-800">{{ label }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.gender" class="mt-1 text-xs text-red-600">{{ form.errors.gender }}</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors" @click="closeForm">
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors"
                    >
                        {{ editing ? 'Guardar cambios' : 'Agregar estudiante' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Delete confirmation modal -->
        <div
            v-if="toDelete"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            @click.self="toDelete = null"
        >
            <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full mx-4">
                <h3 class="font-semibold text-gray-900 text-lg mb-1">¿Eliminar estudiante?</h3>
                <p class="text-gray-500 text-sm mb-6">
                    <strong>{{ toDelete.name }}</strong> será eliminado permanentemente.
                </p>
                <div class="flex gap-3 justify-end">
                    <button
                        @click="toDelete = null"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="deleteStudent"
                        class="px-4 py-2 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import RowActions from '@/Components/RowActions.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    students: { type: Array, default: () => [] },
    genders: { type: Object, default: () => ({}) },
});

const toDelete = ref(null);
const formOpen = ref(false);
const editing = ref(null);

const form = useForm({ name: '', gender: 'brother' });

function openCreate() {
    editing.value = null;
    form.defaults({ name: '', gender: 'brother' });
    form.reset();
    form.clearErrors();
    formOpen.value = true;
}

function openEdit(student) {
    editing.value = student;
    form.defaults({ name: student.name, gender: student.gender });
    form.reset();
    form.clearErrors();
    formOpen.value = true;
}

function closeForm() {
    formOpen.value = false;
}

function submitForm() {
    const opts = { preserveScroll: true, onSuccess: closeForm };
    if (editing.value) {
        form.put(`/students/${editing.value.id}`, opts);
    } else {
        form.post('/students', opts);
    }
}

const selectedClasses = {
    brother: 'border-sky-500 bg-sky-50 ring-1 ring-sky-500',
    sister: 'border-pink-500 bg-pink-50 ring-1 ring-pink-500',
};

function genderSelected(value) {
    return selectedClasses[value] ?? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500';
}

const badgeClasses = {
    brother: 'bg-sky-100 text-sky-800',
    sister: 'bg-pink-100 text-pink-800',
};

const genderLabels = {
    brother: 'Hermano',
    sister: 'Hermana',
};

function genderBadge(gender) {
    return badgeClasses[gender] ?? 'bg-gray-100 text-gray-700';
}

function genderLabel(gender) {
    return genderLabels[gender] ?? gender;
}

function confirmDelete(student) {
    toDelete.value = student;
}

function deleteStudent() {
    router.delete(`/students/${toDelete.value.id}`, {
        onFinish: () => (toDelete.value = null),
    });
}
</script>
