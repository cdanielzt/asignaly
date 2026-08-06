<template>
    <AppLayout :title="meeting.name">

        <!-- ── Toast notifications ─────────────────────────────────────────── -->
        <Transition
            enter-active-class="transition duration-200 ease-out"
            enter-from-class="opacity-0 translate-y-2"
            enter-to-class="opacity-100 translate-y-0"
            leave-active-class="transition duration-150 ease-in"
            leave-from-class="opacity-100 translate-y-0"
            leave-to-class="opacity-0 translate-y-2"
        >
            <div
                v-if="toast.visible"
                class="fixed bottom-24 lg:bottom-6 left-1/2 -translate-x-1/2 z-[100] pointer-events-none w-max max-w-[calc(100vw-2rem)]"
            >
                <div
                    :class="[
                        'flex items-center gap-2.5 px-4 py-3 rounded-xl shadow-2xl text-sm font-medium',
                        toast.type === 'error'
                            ? 'bg-red-600 text-white'
                            : 'bg-[#2e2e34] border border-[#45454d] text-white',
                    ]"
                >
                    <svg v-if="toast.type === 'error'" class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                    </svg>
                    {{ toast.message }}
                </div>
            </div>
        </Transition>

        <!-- ── Picker popover (desktop) / bottom sheet (mobile) ────────────── -->
        <Teleport to="body">
            <div
                v-if="picker.open && picker.sheet"
                class="fixed inset-0 z-[9998] bg-black/50"
            />
            <div
                v-if="picker.open"
                ref="pickerEl"
                :style="picker.sheet
                    ? { zIndex: 9999 }
                    : { position: 'fixed', top: picker.y + 'px', left: picker.x + 'px', width: '280px', zIndex: 9999 }"
                :class="picker.sheet
                    ? 'fixed inset-x-0 bottom-0 rounded-t-2xl pb-safe'
                    : 'rounded-xl'"
                class="bg-white shadow-2xl border border-gray-200 overflow-hidden"
                @click.stop
            >
                <div v-if="picker.sheet" class="mx-auto mt-2.5 h-1 w-9 rounded-full bg-gray-300"></div>
                <!-- Picker header -->
                <div class="px-3 pt-3 pb-2 border-b border-gray-100">
                    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider mb-2">
                        Asignar &mdash; {{ picker.slotLabel }}
                    </p>
                    <!-- Search -->
                    <div class="relative">
                        <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
                        </svg>
                        <input
                            ref="pickerSearchEl"
                            v-model="pickerSearch"
                            type="text"
                            :placeholder="picker.kind === 'student' ? 'Buscar estudiantes…' : 'Buscar hermanos…'"
                            class="w-full pl-8 pr-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent placeholder-gray-400"
                        />
                    </div>
                </div>

                <!-- Catalog list -->
                <div class="overflow-y-auto" :class="picker.sheet ? 'max-h-[50dvh]' : 'max-h-60'">
                    <!-- Clear option -->
                    <button
                        v-if="picker.currentId !== null"
                        class="w-full flex items-center gap-2.5 px-3 py-2.5 text-left hover:bg-red-50 transition-colors group border-b border-gray-100"
                        @click="handleAssign(null)"
                    >
                        <span class="w-6 h-6 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                            <svg class="w-3 h-3 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </span>
                        <span class="text-sm font-medium text-red-600 group-hover:text-red-700">Limpiar asignación</span>
                    </button>

                    <!-- No results -->
                    <div v-if="filteredCatalog.length === 0" class="px-3 py-6 text-center text-sm text-gray-400">
                        Ningún {{ picker.kind === 'student' ? 'estudiante' : 'hermano' }} coincide con tu búsqueda.
                    </div>

                    <!-- Catalog rows -->
                    <button
                        v-for="entry in filteredCatalog"
                        :key="entry.id"
                        :class="[
                            'w-full flex items-center gap-2.5 px-3 py-2.5 text-left transition-colors cursor-pointer',
                            entry._isCurrent ? 'bg-indigo-50 hover:bg-indigo-100' : 'hover:bg-gray-50 bg-white',
                        ]"
                        @click="handleAssign(entry.id)"
                    >
                        <!-- Checkmark or spacer -->
                        <span class="w-4 h-4 shrink-0 flex items-center justify-center">
                            <svg
                                v-if="entry._isCurrent"
                                class="w-4 h-4 text-indigo-600"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>

                        <!-- Name + badges -->
                        <div class="flex-1 min-w-0">
                            <span :class="['block text-sm leading-tight truncate', entry._isCurrent ? 'font-semibold text-indigo-800' : 'font-medium text-gray-800']">
                                {{ entry.name }}
                            </span>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span
                                    v-if="picker.kind === 'attendant'"
                                    :class="['inline-block px-1.5 py-0.5 rounded-full font-medium text-[10px] leading-none', roleBadgeClass(entry.role)]"
                                >
                                    {{ roleAbbr(entry.role) }}
                                </span>
                                <span
                                    v-else
                                    :class="['inline-block px-1.5 py-0.5 rounded-full font-medium text-[10px] leading-none', genderBadgeClass(entry.gender)]"
                                >
                                    {{ genderLabel(entry.gender) }}
                                </span>
                                <span v-if="entry._inMeeting" class="text-[10px] font-medium text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full leading-none">
                                    En esta reunión
                                </span>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </Teleport>

        <!-- ── Page header ──────────────────────────────────────────────────── -->
        <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6 sm:mb-8">
            <div>
                <Link
                    href="/meetings"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 font-medium mb-3 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Todos los programas
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ meeting.name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ localWeeks.length }} semana{{ localWeeks.length !== 1 ? 's' : '' }} &middot;
                    Programa de reunión entre semana
                </p>
            </div>

            <!-- PDF buttons -->
            <div v-if="localWeeks.length" class="flex items-center gap-2 shrink-0 sm:mt-1">
                <button
                    type="button"
                    class="shrink-0 justify-center inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium w-11 h-11 sm:w-auto sm:h-auto sm:px-4 sm:py-2.5 rounded-lg border border-gray-200 transition-colors"
                    title="Vista previa PDF"
                    @click="pdfPreviewOpen = true"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    <span class="hidden sm:inline">Vista previa PDF</span>
                </button>
                <a
                    :href="`/meetings/${meeting.id}/pdf`"
                    class="shrink-0 justify-center inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium w-11 h-11 sm:w-auto sm:h-auto sm:px-4 sm:py-2.5 rounded-lg transition-colors"
                    title="Descargar PDF"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    <span class="hidden sm:inline">Descargar PDF</span>
                </a>
            </div>
        </div>

        <!-- ── Empty state ──────────────────────────────────────────────────── -->
        <div v-if="!localWeeks.length" class="bg-white rounded-xl shadow-sm border border-gray-100 py-20 text-center">
            <div class="w-16 h-16 bg-indigo-50 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-indigo-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-gray-700 font-semibold text-base">Sin semanas</p>
            <p class="text-gray-400 text-sm mt-1">Este programa no tiene semanas aún.</p>
        </div>

        <!-- ── Weeks (tabbed) ───────────────────────────────────────────────── -->
        <div v-else>
            <!-- Week tabs -->
            <div class="relative mb-4">
                <!-- fade hints that more weeks are scrolled off to the right -->
                <div class="sm:hidden pointer-events-none absolute inset-y-0 right-0 w-8 bg-gradient-to-l from-[#141414] to-transparent z-10"></div>
            <div ref="tabsEl" class="flex items-center gap-1 border-b border-gray-200 overflow-x-auto overflow-y-hidden">
                <button
                    v-for="(week, idx) in localWeeks"
                    :key="week.id"
                    type="button"
                    :class="[
                        'shrink-0 sm:flex-1 sm:text-center px-4 py-2.5 text-sm font-medium border-b-2 -mb-px transition-colors',
                        activeWeekIndex === idx
                            ? 'border-indigo-600 text-indigo-700'
                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                    ]"
                    @click="activeWeekIndex = idx"
                >
                    Semana {{ week.week_number }}
                    <span
                        class="block text-[11px] font-normal mt-0.5"
                        :class="activeWeekIndex === idx ? 'text-indigo-400' : 'text-gray-400'"
                    >
                        {{ formatWeekRange(week.start_date, week.end_date) }}
                    </span>
                </button>
            </div>
            </div>

            <template v-for="week in (activeWeek ? [activeWeek] : [])" :key="week.id">
            <!-- no overflow-hidden: it would kill the sticky week header below -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                <!-- Week header — sticks so you keep the week in view while scrolling parts -->
                <div class="sticky top-[calc(env(safe-area-inset-top)_+_3.5rem)] lg:top-0 z-20 rounded-t-xl px-4 sm:px-6 py-4 bg-gray-50 border-b border-gray-100 flex items-center justify-between flex-wrap gap-3">
                    <div>
                        <span class="block text-xs font-bold text-gray-900 uppercase tracking-wide">
                            Semana {{ week.week_number }}
                        </span>
                        <span class="inline-flex items-center gap-1.5 mt-0.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                            <span class="text-xs font-semibold text-indigo-700">
                                {{ formatMeetingDate(week.start_date, 4) }}
                            </span>
                            <span class="text-xs text-gray-400">
                                ({{ formatWeekRange(week.start_date, week.end_date) }})
                            </span>
                        </span>
                    </div>

                    <span
                        v-if="week.bible_reading"
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-indigo-50 text-indigo-700 text-xs font-semibold"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6.042A8.967 8.967 0 006 3.75c-1.052 0-2.062.18-3 .512v14.25A8.987 8.987 0 016 18c2.305 0 4.408.867 6 2.292m0-14.25a8.966 8.966 0 016-2.292c1.052 0 2.062.18 3 .512v14.25A8.987 8.987 0 0018 18a8.967 8.967 0 00-6 2.292m0-14.25v14.25" />
                        </svg>
                        {{ week.bible_reading }}
                    </span>
                </div>

                <!-- Sections -->
                <div class="divide-y divide-gray-100">
                    <div v-for="sectionKey in sectionOrder" :key="`${week.id}-${sectionKey}`" class="px-4 sm:px-6 py-5">
                        <div class="flex items-center justify-between mb-3">
                            <h3 class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider">
                                {{ sections[sectionKey] ?? sectionKey }}
                            </h3>
                            <button
                                v-if="canAddTo(sectionKey)"
                                type="button"
                                class="inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-800 transition-colors"
                                @click="addPart(week, sectionKey)"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                Agregar elemento
                            </button>
                        </div>

                        <div class="space-y-1.5">
                            <div
                                v-for="part in week.sections[sectionKey]"
                                :key="part.id"
                                class="group/part relative rounded-lg hover:bg-gray-50 transition-colors px-4 py-2"
                            >
                                <!-- Remove button -->
                                <button
                                    v-if="canRemoveFrom(sectionKey)"
                                    type="button"
                                    class="absolute top-0 right-0 sm:top-2 sm:right-2 w-11 h-11 sm:w-6 sm:h-6 flex items-center justify-center rounded-md text-gray-300 active:text-red-500 hover:text-red-500 hover:bg-red-50 sm:opacity-0 sm:group-hover/part:opacity-100 transition-all"
                                    title="Eliminar elemento"
                                    @click="confirmRemovePart(week, part)"
                                >
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6M9 7V4a1 1 0 011-1h4a1 1 0 011 1v3M4 7h16" />
                                    </svg>
                                </button>

                                <!-- display_only -->
                                <div v-if="part.type === 'display_only'" class="pr-9 sm:pr-6">
                                    <p class="text-sm text-gray-800">
                                        <span class="font-medium">{{ part.title }}</span>
                                        <span v-if="part.duration" class="text-gray-400"> &mdash; {{ part.duration }}</span>
                                    </p>
                                </div>

                                <!-- editable types -->
                                <div v-else class="flex flex-col sm:flex-row sm:items-center gap-3 pr-11 sm:pr-6">
                                    <!-- Title / duration — stacked on mobile so the title gets the full width -->
                                    <div class="flex-1 min-w-0 flex flex-wrap items-center gap-2">
                                        <input
                                            type="text"
                                            :value="part.title"
                                            placeholder="Título…"
                                            class="w-full sm:w-auto sm:flex-1 min-w-0 text-sm font-medium text-gray-800 bg-transparent border border-gray-200 sm:border-transparent hover:border-gray-200 focus:border-indigo-300 focus:bg-white rounded-md px-2 py-2 sm:py-1 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-colors"
                                            @change="e => updatePart(week, part, { title: e.target.value })"
                                        />
                                        <input
                                            type="text"
                                            :value="part.duration"
                                            placeholder="min."
                                            class="w-20 shrink-0 text-sm text-gray-500 bg-transparent border border-gray-200 sm:border-transparent hover:border-gray-200 focus:border-indigo-300 focus:bg-white rounded-md px-2 py-2 sm:py-1 focus:outline-none focus:ring-2 focus:ring-indigo-100 transition-colors"
                                            @change="e => updatePart(week, part, { duration: e.target.value })"
                                        />
                                        <svg
                                            v-if="isPartSaving(part.id)"
                                            class="w-3.5 h-3.5 text-indigo-400 animate-spin shrink-0"
                                            fill="none" viewBox="0 0 24 24"
                                        >
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                        </svg>
                                    </div>

                                    <!-- Slots -->
                                    <div class="grid grid-cols-2 gap-2 w-full sm:w-auto sm:flex sm:items-center sm:shrink-0">
                                        <AssignmentSlot
                                            v-for="slotDef in slotDefs(part.type)"
                                            :key="slotDef.slot"
                                            :label="slotDef.label"
                                            :assignment="part.assignments?.[slotDef.slot]"
                                            :active="isActivePicker(part.id, slotDef.slot)"
                                            :saving="isSlotSaving(part.id, slotDef.slot)"
                                            :role-badge-class="roleBadgeClass"
                                            :role-abbr="roleAbbr"
                                            :gender-badge-class="genderBadgeClass"
                                            :gender-label="genderLabel"
                                            @open="$event => openPicker($event, week, part, slotDef)"
                                        />
                                    </div>
                                </div>
                            </div>

                            <p v-if="!week.sections[sectionKey]?.length" class="text-xs text-gray-300 italic px-1">
                                Sin elementos en esta sección.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            </template>
        </div>

        <!-- ── PDF Preview modal ────────────────────────────────────────────── -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-200 ease-out"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition duration-150 ease-in"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div
                    v-if="pdfPreviewOpen"
                    class="fixed inset-0 z-[200] flex items-center justify-center p-0 sm:p-4"
                    @click.self="pdfPreviewOpen = false"
                >
                    <!-- Backdrop -->
                    <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" @click="pdfPreviewOpen = false" />

                    <!-- Modal panel (fullscreen on mobile) -->
                    <div class="relative z-10 bg-white rounded-none sm:rounded-2xl shadow-2xl flex flex-col w-full max-w-3xl h-[100dvh] sm:h-[90vh] pb-safe sm:pb-0">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">{{ meeting.name }}</h2>
                                <p class="text-xs text-gray-400 mt-0.5">Reunión Vida y Ministerio Cristianos</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a
                                    :href="`/meetings/${meeting.id}/pdf`"
                                    class="inline-flex items-center gap-1.5 text-sm font-medium text-indigo-600 hover:text-indigo-800 transition-colors px-3 py-1.5 rounded-lg hover:bg-indigo-50"
                                >
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                    </svg>
                                    Descargar
                                </a>
                                <button
                                    type="button"
                                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
                                    @click="pdfPreviewOpen = false"
                                >
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <!-- iframe -->
                        <iframe
                            :src="`/meetings/${meeting.id}/pdf?inline=1`"
                            class="flex-1 w-full rounded-b-2xl"
                            style="border: none;"
                            title="Vista previa PDF"
                        />
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Remove-part confirmation modal -->
        <div
            v-if="toRemove"
            class="fixed inset-0 bg-black/40 flex items-center justify-center z-50"
            @click.self="toRemove = null"
        >
            <div class="bg-white rounded-2xl shadow-xl p-6 max-w-sm w-full mx-4">
                <h3 class="font-semibold text-gray-900 text-lg mb-1">¿Eliminar elemento?</h3>
                <p class="text-gray-500 text-sm mb-6">
                    <strong>{{ toRemove.part.title || 'Este elemento' }}</strong> será eliminado permanentemente del programa.
                </p>
                <div class="flex gap-3 justify-end">
                    <button
                        @click="toRemove = null"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors"
                    >
                        Cancelar
                    </button>
                    <button
                        @click="removePart"
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
import { ref, computed, h, nextTick, onUnmounted, watch } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// ── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
    meeting:    { type: Object, required: true },
    weeks:      { type: Array,  default: () => [] },
    sections:   { type: Object, default: () => ({}) },
    partTypes:  { type: Object, default: () => ({}) },
    attendants: { type: Array,  default: () => [] },
    students:   { type: Array,  default: () => [] },
});

const sectionOrder = ['header', 'tesoros', 'maestros', 'vida_cristiana', 'closing'];

// ── Local mutable copy of weeks ──────────────────────────────────────────────
const localWeeks = ref(JSON.parse(JSON.stringify(props.weeks)));

// ── Week tabs ────────────────────────────────────────────────────────────────
const activeWeekIndex = ref(0);
const activeWeek = computed(() => localWeeks.value[activeWeekIndex.value] ?? null);

// Keep the selected week visible when the strip overflows off-screen on mobile
const tabsEl = ref(null);
watch(activeWeekIndex, idx => {
    tabsEl.value?.children[idx]?.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
});

// ── PDF preview modal ────────────────────────────────────────────────────────
const pdfPreviewOpen = ref(false);

function handlePdfModalKeydown(e) {
    if (e.key === 'Escape' && pdfPreviewOpen.value) {
        e.preventDefault();
        pdfPreviewOpen.value = false;
    }
}
document.addEventListener('keydown', handlePdfModalKeydown);
onUnmounted(() => document.removeEventListener('keydown', handlePdfModalKeydown));

// ── CSRF token ───────────────────────────────────────────────────────────────
import { csrfHeaders } from '@/csrf';

// ── Toast ────────────────────────────────────────────────────────────────────
const toast = ref({ visible: false, message: '', type: 'error' });
let toastTimer = null;

function showToast(message, type = 'error') {
    if (toastTimer) clearTimeout(toastTimer);
    toast.value = { visible: true, message, type };
    toastTimer = setTimeout(() => { toast.value.visible = false; }, 3000);
}

onUnmounted(() => {
    if (toastTimer) clearTimeout(toastTimer);
});

// ── AssignmentSlot — small inline component for a single slot button ────────
const AssignmentSlot = {
    props: {
        label:            { type: String, required: true },
        assignment:       { type: Object, default: null },
        active:           { type: Boolean, default: false },
        saving:           { type: Boolean, default: false },
        roleBadgeClass:   { type: Function, required: true },
        roleAbbr:         { type: Function, required: true },
        genderBadgeClass: { type: Function, required: true },
        genderLabel:      { type: Function, required: true },
    },
    emits: ['open'],
    setup(slotProps, { emit }) {
        return () => {
            const who = slotProps.assignment?.assignable ?? null;

            const badge = who
                ? (who.kind === 'attendant'
                    ? h('span', {
                        class: ['inline-block shrink-0 px-1.5 py-0.5 rounded-full font-medium text-[10px] leading-none', slotProps.roleBadgeClass(who.role)],
                    }, slotProps.roleAbbr(who.role))
                    : h('span', {
                        class: ['inline-block shrink-0 px-1.5 py-0.5 rounded-full font-medium text-[10px] leading-none', slotProps.genderBadgeClass(who.gender)],
                    }, slotProps.genderLabel(who.gender)))
                : null;

            return h('button', {
                type: 'button',
                class: [
                    'relative w-full sm:w-40 min-h-[48px] sm:min-h-0 text-left rounded-lg border px-3 py-1.5 transition-colors focus:outline-none',
                    slotProps.active
                        ? 'border-indigo-300 ring-2 ring-indigo-100 bg-indigo-50/50'
                        : 'border-gray-200 hover:border-indigo-200 hover:bg-indigo-50/30',
                ],
                onClick: (e) => emit('open', e),
            }, [
                h('span', { class: 'block text-[10px] font-semibold text-gray-400 uppercase tracking-wide leading-tight' }, slotProps.label),
                who
                    ? h('div', { class: 'flex items-center gap-1.5 min-w-0' }, [
                        h('span', { class: 'text-sm font-semibold text-gray-900 leading-tight truncate' }, who.name),
                        badge,
                    ])
                    : h('span', { class: 'text-sm text-gray-300 font-medium select-none' }, '— Asignar —'),
                slotProps.saving
                    ? h('span', { class: 'absolute inset-0 flex items-center justify-center bg-gray-100/80 rounded-lg' }, [
                        h('svg', { class: 'w-4 h-4 text-indigo-500 animate-spin', fill: 'none', viewBox: '0 0 24 24' }, [
                            h('circle', { class: 'opacity-25', cx: '12', cy: '12', r: '10', stroke: 'currentColor', 'stroke-width': '4' }),
                            h('path', { class: 'opacity-75', fill: 'currentColor', d: 'M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z' }),
                        ]),
                    ])
                    : null,
            ]);
        };
    },
};

// ── Slot definitions per part type ──────────────────────────────────────────
function slotDefs(type) {
    if (type === 'student_helper') {
        return [
            { slot: 'main',   label: 'Estudiante', kind: 'student' },
            { slot: 'helper', label: 'Ayudante',   kind: 'student' },
        ];
    }
    if (type === 'dual_attendant') {
        return [
            { slot: 'main',   label: 'Conductor', kind: 'attendant' },
            { slot: 'helper', label: 'Lector',    kind: 'attendant' },
        ];
    }
    if (type === 'single_attendant') {
        return [{ slot: 'main', label: 'Asignado', kind: 'attendant' }];
    }
    if (type === 'single_student') {
        return [{ slot: 'main', label: 'Asignado', kind: 'student' }];
    }
    return [];
}

function catalogFor(kind) {
    return kind === 'student' ? props.students : props.attendants;
}

// ── Add / remove part config ─────────────────────────────────────────────────
const addableSections = {
    maestros:        'student_helper',
    vida_cristiana:  'single_attendant',
};
const removableSections = new Set(['tesoros', 'maestros', 'vida_cristiana']);

function canAddTo(sectionKey) {
    return Object.prototype.hasOwnProperty.call(addableSections, sectionKey);
}
function canRemoveFrom(sectionKey) {
    return removableSections.has(sectionKey);
}

// ── Saving indicators ────────────────────────────────────────────────────────
// Part-level (title/duration) saving set, keyed by part id
const savingParts = ref(new Set());
function isPartSaving(partId) {
    return savingParts.value.has(partId);
}
function setPartSaving(partId, on) {
    const next = new Set(savingParts.value);
    if (on) next.add(partId); else next.delete(partId);
    savingParts.value = next;
}

// Slot-level (assignment) saving set, keyed by `${partId}:${slot}`
const savingSlots = ref(new Set());
function slotSavingKey(partId, slot) {
    return `${partId}:${slot}`;
}
function isSlotSaving(partId, slot) {
    return savingSlots.value.has(slotSavingKey(partId, slot));
}
function setSlotSaving(partId, slot, on) {
    const key = slotSavingKey(partId, slot);
    const next = new Set(savingSlots.value);
    if (on) next.add(key); else next.delete(key);
    savingSlots.value = next;
}

// ── Inline title/duration editing ────────────────────────────────────────────
let debounceTimers = {};

async function updatePart(week, part, changes) {
    const previous = { title: part.title, duration: part.duration };

    // Optimistic update
    Object.assign(part, changes);

    setPartSaving(part.id, true);

    try {
        const response = await fetch(`/meeting-parts/${part.id}`, {
            method:  'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                ...csrfHeaders(),
            },
            body: JSON.stringify({ title: part.title, duration: part.duration }),
        });

        if (response.status === 422) {
            const data = await response.json().catch(() => ({}));
            Object.assign(part, previous);
            showToast(data?.message ?? 'No se pudieron guardar los cambios. Verifica los valores.', 'error');
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json().catch(() => null);
        if (data?.part) {
            part.title = data.part.title ?? part.title;
            part.duration = data.part.duration ?? part.duration;
        }
    } catch (err) {
        Object.assign(part, previous);
        showToast('No se pudo guardar. Por favor intenta de nuevo.', 'error');
    } finally {
        setPartSaving(part.id, false);
    }
}

// ── Add part ─────────────────────────────────────────────────────────────────
async function addPart(week, sectionKey) {
    const type = addableSections[sectionKey];
    if (!type) return;

    try {
        const response = await fetch('/meeting-parts', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                ...csrfHeaders(),
            },
            body: JSON.stringify({
                meeting_week_id: week.id,
                section: sectionKey,
                type,
                title: '',
                duration: '',
            }),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = await response.json();
        const part = data?.part ?? data;
        if (part) {
            if (!Array.isArray(week.sections[sectionKey])) week.sections[sectionKey] = [];
            week.sections[sectionKey].push(part);
        }
    } catch (err) {
        showToast('No se pudo agregar el elemento. Por favor intenta de nuevo.', 'error');
    }
}

// ── Remove part ──────────────────────────────────────────────────────────────
const toRemove = ref(null); // { week, part }

function confirmRemovePart(week, part) {
    toRemove.value = { week, part };
}

async function removePart() {
    if (!toRemove.value) return;
    const { week, part } = toRemove.value;

    try {
        const response = await fetch(`/meeting-parts/${part.id}`, {
            method:  'DELETE',
            headers: {
                'Accept':       'application/json',
                ...csrfHeaders(),
            },
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const list = week.sections[part.section];
        if (Array.isArray(list)) {
            const idx = list.findIndex(p => p.id === part.id);
            if (idx !== -1) list.splice(idx, 1);
        }
    } catch (err) {
        showToast('No se pudo eliminar el elemento. Por favor intenta de nuevo.', 'error');
    } finally {
        toRemove.value = null;
    }
}

// ── Picker state ─────────────────────────────────────────────────────────────
const pickerEl       = ref(null);
const pickerSearchEl = ref(null);
const pickerSearch   = ref('');

const picker = ref({
    open:         false,
    sheet:        false,      // bottom-sheet mode on mobile
    weekId:       null,
    partId:       null,
    slot:         null,       // 'main' | 'helper'
    slotLabel:    '',
    kind:         null,       // 'attendant' | 'student'
    assignmentId: null,
    currentId:    null,
    x: 0,
    y: 0,
});

function isActivePicker(partId, slot) {
    return picker.value.open && picker.value.partId === partId && picker.value.slot === slot;
}

// Catalog entries (by kind+id) already used elsewhere in the SAME week.
// "Consejero" (header, position 2) is exempt from the conflict rule — mirrors
// the backend check in MeetingAssignmentController so the picker doesn't grey
// out someone who could actually be selected.
function isConsejeroPart(part) {
    return part?.section === 'header' && part?.position === 2;
}

const meetingOccupiedIds = computed(() => {
    if (!picker.value.open) return new Set();
    const week = localWeeks.value.find(w => w.id === picker.value.weekId);
    if (!week) return new Set();

    const occupied = new Set();

    const { part: pickedPart } = findPart(picker.value.weekId, picker.value.partId);
    if (isConsejeroPart(pickedPart)) return occupied;

    for (const sectionKey of sectionOrder) {
        const parts = week.sections?.[sectionKey] ?? [];
        for (const p of parts) {
            if (isConsejeroPart(p)) continue;
            for (const [slotKey, assignment] of Object.entries(p.assignments ?? {})) {
                if (p.id === picker.value.partId && slotKey === picker.value.slot) continue;
                const who = assignment?.assignable;
                if (who?.id != null && who?.kind === picker.value.kind) {
                    occupied.add(who.id);
                }
            }
        }
    }
    return occupied;
});

const filteredCatalog = computed(() => {
    const search = pickerSearch.value.trim().toLowerCase();
    const list = catalogFor(picker.value.kind);
    return list
        .filter(e => !search || e.name.toLowerCase().includes(search))
        .map(e => ({
            ...e,
            _isCurrent: e.id === picker.value.currentId,
            _inMeeting: meetingOccupiedIds.value.has(e.id),
        }));
});

// ── Open / close picker ───────────────────────────────────────────────────────
function openPicker(event, week, part, slotDef) {
    const assignment = part.assignments?.[slotDef.slot] ?? null;

    // Bottom sheet on mobile, positioned popover on desktop
    const sheet = window.innerWidth < 640;

    const cell = event.currentTarget;
    const rect = cell.getBoundingClientRect();
    const pickerHeight = 340;
    const pickerWidth  = 280;

    let x = rect.left;
    let y = rect.bottom + 6;

    if (x + pickerWidth > window.innerWidth - 8) {
        x = window.innerWidth - pickerWidth - 8;
    }
    if (y + pickerHeight > window.innerHeight - 8) {
        y = rect.top - pickerHeight - 6;
        if (y < 8) y = 8;
    }

    picker.value = {
        open:         true,
        sheet,
        weekId:       week.id,
        partId:       part.id,
        slot:         slotDef.slot,
        slotLabel:    slotDef.label,
        kind:         slotDef.kind,
        assignmentId: assignment?.id ?? null,
        currentId:    assignment?.assignable?.id ?? null,
        x,
        y,
    };

    pickerSearch.value = '';

    nextTick(() => {
        if (!sheet) pickerSearchEl.value?.focus(); // don't pop the keyboard on mobile
        document.addEventListener('click', handleOutsideClick, { capture: true });
        document.addEventListener('keydown', handlePickerKeydown);
    });
}

function closePicker() {
    picker.value.open = false;
    document.removeEventListener('click', handleOutsideClick, { capture: true });
    document.removeEventListener('keydown', handlePickerKeydown);
}

function handleOutsideClick(e) {
    if (pickerEl.value && !pickerEl.value.contains(e.target)) {
        closePicker();
    }
}

function handlePickerKeydown(e) {
    if (e.key === 'Escape') {
        e.preventDefault();
        closePicker();
    }
}

onUnmounted(() => {
    document.removeEventListener('click', handleOutsideClick, { capture: true });
    document.removeEventListener('keydown', handlePickerKeydown);
});

// ── Assignment handler ────────────────────────────────────────────────────────
function findPart(weekId, partId) {
    const week = localWeeks.value.find(w => w.id === weekId);
    if (!week) return { week: null, part: null };
    for (const sectionKey of sectionOrder) {
        const parts = week.sections?.[sectionKey] ?? [];
        const part = parts.find(p => p.id === partId);
        if (part) return { week, part };
    }
    return { week, part: null };
}

// Apply server-driven side-effect assignments (e.g. Presidente → Oración inicial)
// to the local copy so the UI reflects them without a full reload.
function applySyncedAssignments(weekId, synced) {
    if (!Array.isArray(synced) || !synced.length) return;
    const week = localWeeks.value.find(w => w.id === weekId);
    if (!week) return;

    for (const sectionKey of sectionOrder) {
        const parts = week.sections?.[sectionKey] ?? [];
        for (const part of parts) {
            for (const slotKey of Object.keys(part.assignments ?? {})) {
                const assignment = part.assignments[slotKey];
                const match = synced.find(s => s.assignment_id === assignment?.id);
                if (match) {
                    assignment.assignable = match.assignable;
                }
            }
        }
    }
}

async function handleAssign(targetId) {
    const { weekId, partId, slot, assignmentId, kind } = picker.value;

    if (!assignmentId) {
        showToast('No se encontró el registro de asignación para esta posición.', 'error');
        closePicker();
        return;
    }

    const { week, part } = findPart(weekId, partId);
    if (!part || !part.assignments?.[slot]) { closePicker(); return; }

    const previousAssignable = part.assignments[slot].assignable
        ? { ...part.assignments[slot].assignable }
        : null;

    // --- Optimistic update ---
    const newAssignable = targetId !== null
        ? (catalogFor(kind).find(e => e.id === targetId) ?? null)
        : null;

    part.assignments[slot].assignable = newAssignable
        ? { id: newAssignable.id, name: newAssignable.name, kind, role: newAssignable.role ?? null, gender: newAssignable.gender ?? null }
        : null;

    closePicker();
    setSlotSaving(partId, slot, true);

    try {
        const response = await fetch(`/meeting-assignments/${assignmentId}`, {
            method:  'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept':       'application/json',
                ...csrfHeaders(),
            },
            body: JSON.stringify({
                assignable_kind: targetId !== null ? kind : null,
                assignable_id:   targetId,
            }),
        });

        const data = await response.json().catch(() => ({}));

        if (response.status === 422) {
            part.assignments[slot].assignable = previousAssignable;
            const msg = data?.errors?.assignable_id?.[0]
                ?? data?.message
                ?? 'Esta persona ya está asignada a otra parte en esta reunión.';
            showToast(msg, 'error');
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        part.assignments[slot].assignable = data?.assignable ?? null;
        applySyncedAssignments(weekId, data?.synced);

    } catch (err) {
        part.assignments[slot].assignable = previousAssignable;
        showToast('No se pudo guardar. Por favor intenta de nuevo.', 'error');
    } finally {
        setSlotSaving(partId, slot, false);
    }
}

// ── Date helpers ──────────────────────────────────────────────────────────────
function formatMeetingDate(mondayStr, offset) {
    const d = new Date(mondayStr + 'T00:00:00');
    d.setDate(d.getDate() + offset);
    return d.toLocaleDateString('es-MX', { weekday: 'short', month: 'short', day: 'numeric' });
}

function formatWeekRange(startStr, endStr) {
    const start = new Date(startStr + 'T00:00:00');
    const end   = new Date(endStr   + 'T00:00:00');
    const startFmt = start.toLocaleDateString('es-MX', { month: 'short', day: 'numeric' });
    const endFmt   = end.toLocaleDateString('es-MX', { day: 'numeric' });
    if (start.getMonth() === end.getMonth()) return `${startFmt}–${endFmt}`;
    const endFull = end.toLocaleDateString('es-MX', { month: 'short', day: 'numeric' });
    return `${startFmt} – ${endFull}`;
}

// ── Badge helpers ─────────────────────────────────────────────────────────────
function roleBadgeClass(role) {
    if (role === 'Elder')               return 'bg-amber-100 text-amber-800';
    if (role === 'Ministerial Servant') return 'bg-sky-100 text-sky-800';
    if (role === 'Publisher')           return 'bg-green-100 text-green-800';
    return 'bg-gray-100 text-gray-600';
}

function roleAbbr(role) {
    if (role === 'Elder')               return 'Anciano';
    if (role === 'Ministerial Servant') return 'S.M.';
    if (role === 'Publisher')           return 'Publicador';
    return role;
}

function genderBadgeClass(gender) {
    if (gender === 'brother') return 'bg-sky-100 text-sky-800';
    if (gender === 'sister')  return 'bg-pink-100 text-pink-800';
    return 'bg-gray-100 text-gray-600';
}

function genderLabel(gender) {
    if (gender === 'brother') return 'Hermano';
    if (gender === 'sister')  return 'Hermana';
    return gender;
}
</script>
