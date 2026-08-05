<template>
    <AppLayout title="Acomodadores">
        <div class="flex items-center justify-between mb-6 gap-3">
            <p class="text-sm text-gray-500">
                {{ schedules.length }} programa{{ schedules.length !== 1 ? 's' : '' }} en total
            </p>
            <div class="flex items-center gap-2">
                <!-- View toggle -->
                <div class="inline-flex rounded-lg border border-gray-200 bg-white p-0.5">
                    <button
                        type="button"
                        title="Vista de cuadrícula"
                        class="w-9 h-9 flex items-center justify-center rounded-md transition-colors"
                        :class="viewMode === 'grid' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:text-gray-600'"
                        @click="setView('grid')"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                    </button>
                    <button
                        type="button"
                        title="Vista de lista"
                        class="w-9 h-9 flex items-center justify-center rounded-md transition-colors"
                        :class="viewMode === 'list' ? 'bg-indigo-50 text-indigo-600' : 'text-gray-400 hover:text-gray-600'"
                        @click="setView('list')"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>

                <button
                    type="button"
                    class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors"
                    @click="openCreate"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                    </svg>
                    Nuevo programa
                </button>
            </div>
        </div>

        <!-- Grid view -->
        <div v-if="schedules.length && viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div
                v-for="schedule in schedules"
                :key="schedule.id"
                class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 flex items-center justify-between hover:shadow-md hover:border-indigo-100 transition-all group cursor-pointer"
                @click="visit(schedule)"
            >
                <div class="flex items-center gap-4 min-w-0">
                    <!-- Calendar icon block -->
                    <div class="w-12 h-12 bg-indigo-50 rounded-lg flex flex-col items-center justify-center shrink-0 group-hover:bg-indigo-100 transition-colors">
                        <span class="text-indigo-600 text-[10px] font-bold uppercase tracking-wide leading-none">
                            {{ monthAbbr(schedule.month) }}
                        </span>
                        <span class="text-indigo-800 text-lg font-bold leading-tight">
                            {{ schedule.year.toString().slice(-2) }}
                        </span>
                    </div>

                    <div class="min-w-0">
                        <p class="font-semibold text-gray-900 text-sm leading-tight truncate">{{ schedule.name }}</p>
                        <p class="text-xs text-gray-400 mt-0.5">Programa de acomodadores</p>
                    </div>
                </div>

                <div class="flex items-center gap-1 shrink-0" @click.stop>
                    <Link
                        :href="`/schedules/${schedule.id}`"
                        class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors px-2 py-2"
                    >
                        Ver
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <RowActions>
                        <button type="button" class="menu-item-danger" @click="askDelete(schedule)">Eliminar</button>
                    </RowActions>
                </div>
            </div>
        </div>

        <!-- List view -->
        <div v-else-if="schedules.length" class="bg-white rounded-xl shadow-sm border border-gray-100 divide-y divide-gray-100">
            <div
                v-for="schedule in schedules"
                :key="schedule.id"
                class="flex items-center justify-between px-4 sm:px-5 py-3 hover:bg-indigo-50/40 transition-colors cursor-pointer group"
                @click="visit(schedule)"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center shrink-0 group-hover:bg-indigo-100 transition-colors">
                        <span class="text-indigo-600 text-[10px] font-bold uppercase leading-none">{{ monthAbbr(schedule.month) }}</span>
                    </div>
                    <div class="min-w-0">
                        <p class="font-medium text-gray-900 text-sm truncate">{{ schedule.name }}</p>
                    </div>
                </div>

                <div class="flex items-center gap-1 shrink-0" @click.stop>
                    <Link
                        :href="`/schedules/${schedule.id}`"
                        class="inline-flex items-center gap-1.5 text-indigo-600 hover:text-indigo-800 text-sm font-medium transition-colors px-2 py-2"
                    >
                        Ver
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                    <RowActions>
                        <button type="button" class="menu-item-danger" @click="askDelete(schedule)">Eliminar</button>
                    </RowActions>
                </div>
            </div>
        </div>

        <!-- Empty state -->
        <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 py-20 text-center">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-gray-700 font-semibold text-base">Sin programas aún</p>
            <p class="text-gray-400 text-sm mt-1 mb-6">Crea tu primer programa mensual para comenzar.</p>
            <button
                type="button"
                class="inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors"
                @click="openCreate"
            >
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Crear primer programa
            </button>
        </div>

        <!-- Create modal -->
        <Modal :show="formOpen" title="Nuevo programa" @close="formOpen = false">
            <p class="text-sm text-gray-500 mb-5 -mt-1">Elige un mes y año para crear un nuevo programa de acomodadores.</p>
            <form @submit.prevent="submitForm" class="space-y-5">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Mes</label>
                    <select
                        v-model="form.month"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent cursor-pointer"
                        :class="{ 'border-red-400 focus:ring-red-400': form.errors.month }"
                    >
                        <option value="" disabled>Selecciona un mes…</option>
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                    <p v-if="form.errors.month" class="mt-1 text-xs text-red-600">{{ form.errors.month }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1.5">Año</label>
                    <input
                        v-model.number="form.year"
                        type="number"
                        :min="2020"
                        :max="2099"
                        class="w-full border border-gray-200 rounded-lg px-3.5 py-2.5 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                        :class="{ 'border-red-400 focus:ring-red-400': form.errors.year }"
                    />
                    <p v-if="form.errors.year" class="mt-1 text-xs text-red-600">{{ form.errors.year }}</p>
                </div>

                <div class="flex items-center justify-end gap-3 pt-2">
                    <button type="button" class="text-sm font-medium text-gray-500 hover:text-gray-700 transition-colors" @click="formOpen = false">
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors"
                    >
                        {{ form.processing ? 'Creando…' : 'Crear programa' }}
                    </button>
                </div>
            </form>
        </Modal>

        <!-- Delete confirmation -->
        <Modal :show="!!deleting" title="Eliminar programa" @close="deleting = null">
            <p class="text-sm text-gray-300 -mt-1">
                ¿Eliminar el programa de <span class="font-semibold text-white">{{ deleting?.name }}</span>?
                Se perderán todas las asignaciones. Esta acción no se puede deshacer.
            </p>
            <div class="flex items-center justify-end gap-3 pt-5">
                <button type="button" class="text-sm font-medium text-gray-400 hover:text-gray-200 transition-colors" @click="deleting = null">
                    Cancelar
                </button>
                <button
                    type="button"
                    :disabled="deleteProcessing"
                    class="bg-red-600 hover:bg-red-700 disabled:opacity-60 text-white text-sm font-medium px-5 py-2.5 rounded-lg transition-colors"
                    @click="confirmDelete"
                >
                    {{ deleteProcessing ? 'Eliminando…' : 'Eliminar' }}
                </button>
            </div>
        </Modal>
    </AppLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import RowActions from '@/Components/RowActions.vue';
import Modal from '@/Components/Modal.vue';

defineProps({
    schedules: {
        type: Array,
        default: () => [],
    },
});

const MONTH_ABBRS = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

const months = MONTH_ABBRS.map((_, i) => ({
    value: i + 1,
    label: ['Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'][i],
}));

const viewMode = ref(localStorage.getItem('schedules.view') || 'grid');

function setView(mode) {
    viewMode.value = mode;
    localStorage.setItem('schedules.view', mode);
}

const formOpen = ref(false);
const form = useForm({ month: '', year: new Date().getFullYear() });

function openCreate() {
    form.reset();
    form.clearErrors();
    formOpen.value = true;
}

function submitForm() {
    form.post('/schedules', { onSuccess: () => (formOpen.value = false) });
}

function monthAbbr(month) {
    return MONTH_ABBRS[(month - 1)] ?? '—';
}

function visit(schedule) {
    router.visit(`/schedules/${schedule.id}`);
}

const deleting = ref(null);
const deleteProcessing = ref(false);

function askDelete(schedule) {
    deleting.value = schedule;
}

function confirmDelete() {
    deleteProcessing.value = true;
    router.delete(`/schedules/${deleting.value.id}`, {
        onFinish: () => {
            deleteProcessing.value = false;
            deleting.value = null;
        },
    });
}
</script>
