<template>
    <AdminLayout title="Congregaciones">
        <div class="flex justify-between items-center mb-6">
            <p class="text-sm text-gray-500">{{ congregations.length }} congregación(es) registrada(s)</p>
            <button
                type="button"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors"
                @click="openCreate"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Nueva congregación
            </button>
        </div>

        <div v-if="congregations.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <p class="text-gray-500 text-sm">No hay congregaciones registradas.</p>
        </div>

        <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
            <!-- Mobile card list (tap row = manage) -->
            <ul class="md:hidden divide-y divide-gray-50">
                <li v-for="c in congregations" :key="c.id" class="flex items-center gap-2 pl-4 pr-2 py-2">
                    <button type="button" class="flex-1 min-w-0 text-left py-1.5" @click="switchTo(c)">
                        <p class="text-base font-medium text-gray-900 truncate">{{ c.name }}</p>
                        <p class="text-sm text-gray-500 truncate">
                            {{ c.city || '—' }} · {{ c.users_count }} usuario{{ c.users_count !== 1 ? 's' : '' }} ·
                            {{ c.attendants_count }} hermano{{ c.attendants_count !== 1 ? 's' : '' }}
                        </p>
                        <span class="inline-flex items-center gap-1 mt-1 text-xs font-medium text-indigo-600">
                            Gestionar
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
                    </button>
                    <RowActions>
                        <Link :href="`/admin/users?congregation_id=${c.id}`" class="menu-item">Usuarios</Link>
                        <button type="button" class="menu-item" @click="openEdit(c)">Editar</button>
                        <div class="menu-divider"></div>
                        <button type="button" class="menu-item-danger" @click="confirmDelete(c)">Eliminar</button>
                    </RowActions>
                </li>
            </ul>

            <table class="min-w-full divide-y divide-gray-100 hidden md:table">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Ciudad</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Usuarios</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Hermanos</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-gray-500 uppercase tracking-wider">Estudiantes</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="c in congregations" :key="c.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ c.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ c.city || '—' }}</td>
                        <td class="px-6 py-4 text-sm text-center text-gray-700">{{ c.users_count }}</td>
                        <td class="px-6 py-4 text-sm text-center text-gray-700">{{ c.attendants_count }}</td>
                        <td class="px-6 py-4 text-sm text-center text-gray-700">{{ c.students_count }}</td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-1">
                                <button
                                    type="button"
                                    class="text-xs text-indigo-600 hover:text-indigo-800 font-medium"
                                    @click="switchTo(c)"
                                >
                                    Gestionar
                                </button>
                                <RowActions>
                                    <Link :href="`/admin/users?congregation_id=${c.id}`" class="menu-item">Usuarios</Link>
                                    <button type="button" class="menu-item" @click="openEdit(c)">Editar</button>
                                    <div class="menu-divider"></div>
                                    <button type="button" class="menu-item-danger" @click="confirmDelete(c)">Eliminar</button>
                                </RowActions>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add / edit modal -->
        <Modal :show="formOpen" :title="editing ? 'Editar congregación' : 'Nueva congregación'" @close="closeForm">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                        placeholder="Nombre de la congregación"
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

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg" @click="closeForm">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg disabled:opacity-50">
                        {{ editing ? 'Guardar cambios' : 'Crear congregación' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Delete confirmation modal -->
        <div v-if="deleting" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
                <h3 class="font-semibold text-gray-900 mb-2">Eliminar congregación</h3>
                <p class="text-sm text-gray-600 mb-4">
                    ¿Seguro que quieres eliminar <strong>{{ deleting.name }}</strong>?
                    Se eliminará toda la información relacionada.
                </p>
                <div class="flex gap-3 justify-end">
                    <button
                        type="button"
                        class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg"
                        @click="deleting = null"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg"
                        @click="doDelete"
                    >
                        Eliminar
                    </button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RowActions from '@/Components/RowActions.vue';
import Modal from '@/Components/Modal.vue';

defineProps({
    congregations: { type: Array, default: () => [] },
});

const deleting = ref(null);
const formOpen = ref(false);
const editing = ref(null);

const form = useForm({ name: '', city: '' });

function openCreate() {
    editing.value = null;
    form.defaults({ name: '', city: '' });
    form.reset();
    form.clearErrors();
    formOpen.value = true;
}

function openEdit(congregation) {
    editing.value = congregation;
    form.defaults({ name: congregation.name, city: congregation.city ?? '' });
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
        form.put(`/admin/congregations/${editing.value.id}`, opts);
    } else {
        form.post('/admin/congregations', opts);
    }
}

function switchTo(congregation) {
    router.post(`/admin/congregations/${congregation.id}/switch`);
}

function confirmDelete(congregation) {
    deleting.value = congregation;
}

function doDelete() {
    router.delete(`/admin/congregations/${deleting.value.id}`, {
        onFinish: () => { deleting.value = null; },
    });
}
</script>
