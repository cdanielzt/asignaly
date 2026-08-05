<template>
    <AdminLayout title="Usuarios">
        <div class="flex flex-wrap items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-3">
                <select
                    v-model="selectedCongregation"
                    class="border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                    @change="filterByCongregation"
                >
                    <option value="">Todas las congregaciones</option>
                    <option v-for="c in congregations" :key="c.id" :value="c.id">{{ c.name }}</option>
                </select>
            </div>

            <button
                type="button"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors"
                @click="openCreate"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Nuevo usuario
            </button>
        </div>

        <div v-if="users.length === 0" class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
            <p class="text-gray-500 text-sm">No hay usuarios registrados.</p>
        </div>

        <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
            <table class="min-w-full divide-y divide-gray-100">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Congregación</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ u.name }}</td>
                        <td class="px-6 py-4 text-sm text-gray-600">{{ u.email }}</td>
                        <td class="px-6 py-4">
                            <span :class="roleBadgeClass(u.role)" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                {{ roleLabel(u.role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">{{ u.congregation?.name ?? '—' }}</td>
                        <td class="px-6 py-4 text-right">
                            <RowActions>
                                <button type="button" class="menu-item" @click="openEdit(u)">Editar</button>
                                <div class="menu-divider"></div>
                                <button type="button" class="menu-item-danger" @click="confirmDelete(u)">Eliminar</button>
                            </RowActions>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Add / edit modal -->
        <Modal :show="formOpen" :title="editing ? 'Editar usuario' : 'Nuevo usuario'" @close="closeForm">
            <form @submit.prevent="submitForm" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nombre *</label>
                    <input v-model="form.name" type="text" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input v-model="form.email" type="email" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    <p v-if="form.errors.email" class="mt-1 text-xs text-red-600">{{ form.errors.email }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Contraseña {{ editing ? '(dejar vacío para no cambiar)' : '*' }}
                    </label>
                    <input v-model="form.password" type="password" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" />
                    <p v-if="form.errors.password" class="mt-1 text-xs text-red-600">{{ form.errors.password }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Rol *</label>
                    <select v-model="form.role" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="member">Miembro</option>
                        <option value="congregation_admin">Admin de congregación</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                    <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
                </div>

                <div v-if="form.role !== 'super_admin'">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Congregación *</label>
                    <select v-model="form.congregation_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">Seleccionar congregación</option>
                        <option v-for="c in congregations" :key="c.id" :value="c.id">{{ c.name }}</option>
                    </select>
                    <p v-if="form.errors.congregation_id" class="mt-1 text-xs text-red-600">{{ form.errors.congregation_id }}</p>
                </div>

                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg" @click="closeForm">
                        Cancelar
                    </button>
                    <button type="submit" :disabled="form.processing" class="px-5 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg disabled:opacity-50">
                        {{ editing ? 'Guardar cambios' : 'Crear usuario' }}
                    </button>
                </div>
            </form>
        </Modal>

        <div v-if="deleting" class="fixed inset-0 bg-black/40 flex items-center justify-center z-50">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-sm mx-4">
                <h3 class="font-semibold text-gray-900 mb-2">Eliminar usuario</h3>
                <p class="text-sm text-gray-600 mb-4">
                    ¿Seguro que quieres eliminar a <strong>{{ deleting.name }}</strong>?
                </p>
                <div class="flex gap-3 justify-end">
                    <button type="button" class="px-4 py-2 text-sm text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg" @click="deleting = null">Cancelar</button>
                    <button type="button" class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg" @click="doDelete">Eliminar</button>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import RowActions from '@/Components/RowActions.vue';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    users:                 { type: Array,  default: () => [] },
    congregations:         { type: Array,  default: () => [] },
    filterCongregationId:  { type: [Number, String], default: '' },
});

const selectedCongregation = ref(props.filterCongregationId ?? '');
const deleting = ref(null);
const formOpen = ref(false);
const editing = ref(null);

const form = useForm({ name: '', email: '', password: '', role: 'member', congregation_id: '' });

function openCreate() {
    editing.value = null;
    form.defaults({ name: '', email: '', password: '', role: 'member', congregation_id: selectedCongregation.value ?? '' });
    form.reset();
    form.clearErrors();
    formOpen.value = true;
}

function openEdit(user) {
    editing.value = user;
    form.defaults({
        name: user.name,
        email: user.email,
        password: '',
        role: user.role,
        congregation_id: user.congregation_id ?? '',
    });
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
        form.put(`/admin/users/${editing.value.id}`, opts);
    } else {
        form.post('/admin/users', opts);
    }
}

function filterByCongregation() {
    const params = selectedCongregation.value ? { congregation_id: selectedCongregation.value } : {};
    router.get('/admin/users', params, { preserveState: true });
}

function roleLabel(role) {
    return { super_admin: 'Super Admin', congregation_admin: 'Admin', member: 'Miembro' }[role] ?? role;
}

function roleBadgeClass(role) {
    return {
        super_admin:        'bg-purple-100 text-purple-700',
        congregation_admin: 'bg-blue-100 text-blue-700',
        member:             'bg-gray-100 text-gray-700',
    }[role] ?? 'bg-gray-100 text-gray-700';
}

function confirmDelete(user) {
    deleting.value = user;
}

function doDelete() {
    router.delete(`/admin/users/${deleting.value.id}`, {
        onFinish: () => { deleting.value = null; },
    });
}
</script>
