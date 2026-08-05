<template>
    <AppLayout title="Hermanos">
        <div class="flex items-center justify-between mb-6">
            <p class="text-sm text-gray-500">{{ attendants.length }} hermano{{ attendants.length !== 1 ? 's' : '' }} en total</p>
            <button
                type="button"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors"
                @click="openCreate"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Agregar hermano
            </button>
        </div>

        <ListToolbar
            v-if="attendants.length"
            v-model:search="search"
            v-model:filter="filterRole"
            v-model:sort="sortBy"
            search-placeholder="Buscar hermanos…"
            filter-all-label="Todas las funciones"
            :filter-options="roles.map(r => ({ value: r, label: roleLabel(r) }))"
            :sort-options="[
                { value: 'name_asc',  label: 'Nombre A–Z' },
                { value: 'name_desc', label: 'Nombre Z–A' },
            ]"
        />

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-visible">
            <!-- No results for current search/filter -->
            <div v-if="attendants.length && !filteredAttendants.length" class="py-12 text-center">
                <p class="text-gray-500 font-medium text-sm">Sin resultados</p>
                <p class="text-gray-400 text-xs mt-1">Prueba con otra búsqueda o filtro.</p>
            </div>

            <!-- Mobile card list (tap row = edit) -->
            <ul v-if="filteredAttendants.length" class="md:hidden divide-y divide-gray-50">
                <li v-for="attendant in filteredAttendants" :key="attendant.id" class="flex items-center gap-2 pl-4 pr-2 py-2 active:bg-gray-50 transition-colors">
                    <button type="button" class="flex-1 min-w-0 text-left py-1.5" @click="openEdit(attendant)">
                        <p class="text-base font-medium text-gray-900 truncate">{{ attendant.name }}</p>
                        <span :class="['inline-flex items-center mt-1 px-2.5 py-0.5 rounded-full text-xs font-medium', roleBadge(attendant.role)]">
                            {{ roleLabel(attendant.role) }}
                        </span>
                    </button>
                    <RowActions>
                        <button type="button" class="menu-item" @click="openEdit(attendant)">Editar</button>
                        <div class="menu-divider"></div>
                        <button type="button" class="menu-item-danger" @click="confirmDelete(attendant)">Eliminar</button>
                    </RowActions>
                </li>
            </ul>

            <table v-if="filteredAttendants.length" class="w-full text-sm hidden md:table">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100">
                        <th class="text-left px-6 py-3.5 font-semibold text-gray-600">Nombre</th>
                        <th class="text-left px-6 py-3.5 font-semibold text-gray-600">Función</th>
                        <th class="px-6 py-3.5 font-semibold text-gray-600 text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    <tr v-for="attendant in filteredAttendants" :key="attendant.id" class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 font-medium text-gray-900">{{ attendant.name }}</td>
                        <td class="px-6 py-4">
                            <span :class="['inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium', roleBadge(attendant.role)]">
                                {{ roleLabel(attendant.role) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <RowActions>
                                <button type="button" class="menu-item" @click="openEdit(attendant)">Editar</button>
                                <div class="menu-divider"></div>
                                <button type="button" class="menu-item-danger" @click="confirmDelete(attendant)">Eliminar</button>
                            </RowActions>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div v-if="!attendants.length" class="py-16 text-center">
                <svg class="w-12 h-12 text-gray-300 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p class="text-gray-500 font-medium">Sin hermanos aún</p>
                <p class="text-gray-400 text-xs mt-1">Agrega tu primer hermano para comenzar.</p>
            </div>
        </div>

        <!-- Add / edit modal -->
        <Modal :show="formOpen" :title="editing ? 'Editar hermano' : 'Agregar hermano'" @close="closeForm">
            <form @submit.prevent="submitForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Nombre completo</label>
                    <input
                        v-model="form.name"
                        type="text"
                        placeholder="ej. Juan López"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-400"
                        :class="{ 'border-red-400 focus:ring-red-400': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="mt-1 text-xs text-red-600">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Función</label>
                    <div class="flex flex-col gap-2">
                        <label
                            v-for="r in roles"
                            :key="r"
                            :class="[
                                'flex items-center gap-3 border rounded-lg px-4 py-3 cursor-pointer transition-colors',
                                form.role === r ? roleSelected(r) : 'border-gray-200 hover:border-gray-300',
                            ]"
                        >
                            <input type="radio" v-model="form.role" :value="r" class="accent-indigo-600" />
                            <span class="text-sm font-medium text-gray-800">{{ roleLabel(r) }}</span>
                        </label>
                    </div>
                    <p v-if="form.errors.role" class="mt-1 text-xs text-red-600">{{ form.errors.role }}</p>
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
                        {{ editing ? 'Guardar cambios' : 'Agregar hermano' }}
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
                <h3 class="font-semibold text-gray-900 text-lg mb-1">¿Eliminar hermano?</h3>
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
                        @click="deleteAttendant"
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
import { ref, computed, onMounted } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import RowActions from '@/Components/RowActions.vue';
import Modal from '@/Components/Modal.vue';
import ListToolbar from '@/Components/ListToolbar.vue';

const props = defineProps({
    attendants: { type: Array, default: () => [] },
    roles: { type: Array, default: () => [] },
});

// ── Search / filter / sort ───────────────────────────────────────────────────
const search = ref('');
const filterRole = ref('');
const sortBy = ref('name_asc');

const norm = s => s.normalize('NFD').replace(/[̀-ͯ]/g, '').toLowerCase();

const filteredAttendants = computed(() => {
    const q = norm(search.value.trim());
    return props.attendants
        .filter(a => (!q || norm(a.name).includes(q)) && (!filterRole.value || a.role === filterRole.value))
        .sort((a, b) => sortBy.value === 'name_desc'
            ? b.name.localeCompare(a.name, 'es')
            : a.name.localeCompare(b.name, 'es'));
});

const toDelete = ref(null);
const formOpen = ref(false);
const editing = ref(null);

const form = useForm({ name: '', role: 'Publisher' });

function openCreate() {
    editing.value = null;
    form.defaults({ name: '', role: 'Publisher' });
    form.reset();
    form.clearErrors();
    formOpen.value = true;
}

function openEdit(attendant) {
    editing.value = attendant;
    form.defaults({ name: attendant.name, role: attendant.role });
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
        form.put(`/attendants/${editing.value.id}`, opts);
    } else {
        form.post('/attendants', opts);
    }
}

// Dashboard "Agregar hermano" quick action links here with ?new=1
onMounted(() => {
    if (new URLSearchParams(window.location.search).has('new')) openCreate();
});

const badgeClasses = {
    'Elder': 'bg-amber-100 text-amber-800',
    'Ministerial Servant': 'bg-sky-100 text-sky-800',
    'Publisher': 'bg-green-100 text-green-800',
};

const roleLabels = {
    'Elder': 'Anciano',
    'Ministerial Servant': 'Siervo Ministerial',
    'Publisher': 'Publicador',
};

const selectedClasses = {
    'Elder': 'border-amber-500 bg-amber-50 ring-1 ring-amber-500',
    'Ministerial Servant': 'border-sky-500 bg-sky-50 ring-1 ring-sky-500',
    'Publisher': 'border-green-500 bg-green-50 ring-1 ring-green-500',
};

function roleBadge(role) {
    return badgeClasses[role] ?? 'bg-gray-100 text-gray-700';
}

function roleLabel(role) {
    return roleLabels[role] ?? role;
}

function roleSelected(r) {
    return selectedClasses[r] ?? 'border-indigo-500 bg-indigo-50 ring-1 ring-indigo-500';
}

function confirmDelete(attendant) {
    toDelete.value = attendant;
}

function deleteAttendant() {
    router.delete(`/attendants/${toDelete.value.id}`, {
        preserveScroll: true,
        onFinish: () => (toDelete.value = null),
    });
}
</script>
