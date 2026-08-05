<template>
    <AppLayout :title="schedule.name">

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
                        Asignar &mdash; {{ roleLabel(picker.roleKey) }}
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
                            placeholder="Buscar hermanos…"
                            class="w-full pl-8 pr-3 py-1.5 text-sm bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:border-transparent placeholder-gray-400"
                        />
                    </div>
                </div>

                <!-- Attendant list -->
                <div class="overflow-y-auto" :class="picker.sheet ? 'max-h-[50dvh]' : 'max-h-60'">
                    <!-- Clear option -->
                    <button
                        v-if="picker.currentAttendantId !== null"
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
                    <div v-if="filteredAttendants.length === 0" class="px-3 py-6 text-center text-sm text-gray-400">
                        Ningún hermano coincide con tu búsqueda.
                    </div>

                    <!-- Attendant rows -->
                    <button
                        v-for="att in filteredAttendants"
                        :key="att.id"
                        :disabled="att._inMeeting"
                        :class="[
                            'w-full flex items-center gap-2.5 px-3 py-2.5 text-left transition-colors border-b border-gray-50 last:border-b-0',
                            att._isCurrent
                                ? 'bg-indigo-50 hover:bg-indigo-100'
                                : att._inMeeting
                                    ? 'opacity-50 cursor-not-allowed bg-white'
                                    : 'hover:bg-gray-50 cursor-pointer bg-white',
                        ]"
                        @click="!att._inMeeting && handleAssign(att.id)"
                    >
                        <!-- Checkmark or spacer -->
                        <span class="w-4 h-4 shrink-0 flex items-center justify-center">
                            <svg
                                v-if="att._isCurrent"
                                class="w-4 h-4 text-indigo-600"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>

                        <!-- Name + badges -->
                        <div class="flex-1 min-w-0">
                            <span :class="['block text-sm leading-tight truncate', att._isCurrent ? 'font-semibold text-indigo-800' : 'font-medium text-gray-800']">
                                {{ att.name }}
                            </span>
                            <div class="flex items-center gap-1.5 mt-0.5 flex-wrap">
                                <span :class="['inline-block px-1.5 py-0.5 rounded-full font-medium text-[10px] leading-none', roleBadgeClass(att.role)]">
                                    {{ roleAbbr(att.role) }}
                                </span>
                                <span v-if="att._inMeeting" class="text-[10px] font-medium text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded-full leading-none">
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
                    href="/schedules"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 font-medium mb-3 transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Todos los programas
                </Link>
                <h1 class="text-2xl font-bold text-gray-900 tracking-tight">{{ schedule.name }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ localWeeks.length }} semana{{ localWeeks.length !== 1 ? 's' : '' }} &middot;
                    Reuniones del viernes y sábado
                </p>
            </div>

            <!-- PDF buttons -->
            <div class="flex items-center gap-2 shrink-0 sm:mt-1">
                <button
                    type="button"
                    class="flex-1 sm:flex-initial justify-center inline-flex items-center gap-2 bg-white hover:bg-gray-50 text-gray-700 text-sm font-medium px-4 py-2.5 rounded-lg border border-gray-200 transition-colors"
                    @click="pdfPreviewOpen = true"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Vista previa PDF
                </button>
                <a
                    :href="`/schedules/${schedule.id}/pdf`"
                    class="flex-1 sm:flex-initial justify-center inline-flex items-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium px-4 py-2.5 rounded-lg transition-colors"
                >
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Descargar PDF
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

        <!-- ── Schedule grid ────────────────────────────────────────────────── -->
        <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">

            <!-- Legend (desktop only) -->
            <div class="px-6 py-3 bg-gray-50 border-b border-gray-100 hidden md:flex items-center gap-6">
                <span class="text-xs font-semibold text-gray-500 uppercase tracking-wide mr-2">Leyenda</span>
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-600">
                    <span class="w-3 h-3 rounded-sm bg-indigo-400 shrink-0"></span>
                    Reunión del viernes
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs text-gray-600">
                    <span class="w-3 h-3 rounded-sm bg-violet-400 shrink-0"></span>
                    Reunión del sábado
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs ml-auto">
                    <span class="inline-block px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800 font-medium text-[10px]">A</span>
                    <span class="text-gray-500">Anciano</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs">
                    <span class="inline-block px-1.5 py-0.5 rounded-full bg-sky-100 text-sky-800 font-medium text-[10px]">SM</span>
                    <span class="text-gray-500">S.M.</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-xs">
                    <span class="inline-block px-1.5 py-0.5 rounded-full bg-green-100 text-green-800 font-medium text-[10px]">P</span>
                    <span class="text-gray-500">Publicador</span>
                </span>
            </div>

            <!-- Mobile: per-week cards with tappable slots -->
            <div class="md:hidden divide-y divide-gray-100">
                <div v-for="week in localWeeks" :key="`m-${week.id}`" class="px-4 py-4">
                    <div class="mb-3">
                        <span class="block text-sm font-bold text-gray-900 uppercase tracking-wide">
                            Semana {{ week.week_number }}
                        </span>
                        <span class="block text-xs text-gray-400 mt-0.5">
                            {{ formatWeekRange(week.start_date, week.end_date) }}
                        </span>
                    </div>

                    <div v-for="day in ['friday', 'saturday']" :key="`m-${week.id}-${day}`" class="mb-3 last:mb-0">
                        <span class="inline-flex items-center gap-1.5 mb-1.5">
                            <span :class="['w-1.5 h-1.5 rounded-full shrink-0', day === 'friday' ? 'bg-indigo-400' : 'bg-violet-400']"></span>
                            <span :class="['text-xs font-semibold', day === 'friday' ? 'text-indigo-700' : 'text-violet-700']">
                                {{ formatMeetingDate(week.start_date, day === 'friday' ? 4 : 5) }}
                            </span>
                        </span>
                        <div class="grid grid-cols-2 gap-2">
                            <button
                                v-for="(label, key) in roles"
                                :key="`m-${day}-${week.id}-${key}`"
                                type="button"
                                class="relative text-left rounded-lg border px-3 py-2 min-h-[56px] transition-colors"
                                :class="isActivePicker(week.id, day, key)
                                    ? 'border-indigo-300 ring-2 ring-indigo-100 bg-indigo-50/50'
                                    : 'border-gray-200 active:bg-indigo-50/40'"
                                @click="openPicker($event, week, day, key)"
                            >
                                <span class="absolute inset-0 flex items-center justify-center bg-white/70 rounded-lg z-10" v-if="isSaving(week.id, day, key)">
                                    <svg class="w-4 h-4 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                    </svg>
                                </span>
                                <span class="block text-[10px] font-semibold text-gray-400 uppercase tracking-wide leading-tight truncate">{{ label }}</span>
                                <span v-if="week.meetings?.[day]?.[key]?.attendant" class="block text-sm font-semibold text-gray-900 leading-tight truncate mt-0.5">
                                    {{ week.meetings[day][key].attendant.name }}
                                </span>
                                <span v-else class="block text-sm text-gray-300 font-medium select-none mt-0.5">— Asignar —</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="overflow-x-auto hidden md:block">
                <table class="w-full text-sm border-collapse">
                    <!-- Column headers -->
                    <thead>
                        <tr class="bg-indigo-900 text-white">
                            <th class="text-left px-5 py-3.5 font-semibold text-indigo-800 text-xs uppercase tracking-wider w-44 border-r border-[#34343b]">
                                Semana
                            </th>
                            <th class="text-left px-5 py-3.5 font-semibold text-indigo-800 text-xs uppercase tracking-wider w-24 border-r border-[#34343b]">
                                Reunión
                            </th>
                            <th
                                v-for="(label, key) in roles"
                                :key="key"
                                class="text-left px-5 py-3.5 font-semibold text-indigo-800 text-xs uppercase tracking-wider border-r border-[#34343b] last:border-r-0"
                            >
                                {{ label }}
                            </th>
                        </tr>
                    </thead>

                    <tbody>
                        <template v-for="(week, weekIndex) in localWeeks" :key="week.id">
                            <!-- Friday row -->
                            <tr :class="['border-b border-gray-100', weekIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50']">
                                <!-- Week label — rowspan 2 -->
                                <td rowspan="2" class="px-5 py-4 border-r border-gray-100 align-top w-44">
                                    <span class="block text-xs font-bold text-gray-900 uppercase tracking-wide">
                                        Semana {{ week.week_number }}
                                    </span>
                                    <span class="block text-xs text-gray-400 mt-0.5">
                                        {{ formatWeekRange(week.start_date, week.end_date) }}
                                    </span>
                                </td>

                                <!-- Friday label -->
                                <td class="px-4 py-4 border-r border-gray-100 w-24 align-top">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 shrink-0"></span>
                                        <span class="text-xs font-semibold text-indigo-700">
                                            {{ formatMeetingDate(week.start_date, 4) }}
                                        </span>
                                    </span>
                                </td>

                                <!-- Friday role cells -->
                                <td
                                    v-for="(label, key) in roles"
                                    :key="`fri-${week.id}-${key}`"
                                    class="px-0 py-0 border-r border-gray-100 last:border-r-0 align-top"
                                >
                                    <div
                                        class="relative group/cell px-5 py-4 min-h-[60px] cursor-pointer hover:bg-indigo-50/60 transition-colors ring-inset focus-visible:outline-none"
                                        :class="{
                                            'ring-2 ring-indigo-300 bg-indigo-50/40': isActivePicker(week.id, 'friday', key),
                                        }"
                                        role="button"
                                        tabindex="0"
                                        :aria-label="`Asignar ${label} para el viernes, semana ${week.week_number}`"
                                        @click="openPicker($event, week, 'friday', key)"
                                        @keydown.enter.space.prevent="openPicker($event, week, 'friday', key)"
                                    >
                                        <!-- Saving spinner -->
                                        <span
                                            v-if="isSaving(week.id, 'friday', key)"
                                            class="absolute inset-0 flex items-center justify-center bg-white/70 z-10"
                                        >
                                            <svg class="w-4 h-4 text-indigo-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                            </svg>
                                        </span>

                                        <!-- Cell content -->
                                        <div v-if="week.meetings?.friday?.[key]?.attendant" class="pr-4">
                                            <span class="block text-sm font-semibold text-gray-900 leading-tight">
                                                {{ week.meetings.friday[key].attendant.name }}
                                            </span>
                                            <span
                                                :class="['inline-block mt-1 px-1.5 py-0.5 rounded-full font-medium text-[10px] leading-none', roleBadgeClass(week.meetings.friday[key].attendant.role)]"
                                            >
                                                {{ roleAbbr(week.meetings.friday[key].attendant.role) }}
                                            </span>
                                        </div>
                                        <span v-else class="text-gray-300 font-medium select-none">&mdash;</span>

                                        <!-- Pencil hint icon -->
                                        <span class="absolute top-2 right-2 opacity-0 group-hover/cell:opacity-100 transition-opacity">
                                            <svg class="w-3 h-3 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828A2 2 0 019 15v-2z" />
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Saturday row -->
                            <tr :class="['border-b border-gray-200', weekIndex % 2 === 0 ? 'bg-white' : 'bg-gray-50']">
                                <!-- Saturday label -->
                                <td class="px-4 py-4 border-r border-gray-100 w-24 align-top">
                                    <span class="inline-flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full bg-violet-400 shrink-0"></span>
                                        <span class="text-xs font-semibold text-violet-700">
                                            {{ formatMeetingDate(week.start_date, 5) }}
                                        </span>
                                    </span>
                                </td>

                                <!-- Saturday role cells -->
                                <td
                                    v-for="(label, key) in roles"
                                    :key="`sat-${week.id}-${key}`"
                                    class="px-0 py-0 border-r border-gray-100 last:border-r-0 align-top"
                                >
                                    <div
                                        class="relative group/cell px-5 py-4 min-h-[60px] cursor-pointer hover:bg-violet-50/60 transition-colors ring-inset focus-visible:outline-none"
                                        :class="{
                                            'ring-2 ring-violet-300 bg-violet-50/40': isActivePicker(week.id, 'saturday', key),
                                        }"
                                        role="button"
                                        tabindex="0"
                                        :aria-label="`Asignar ${label} para el sábado, semana ${week.week_number}`"
                                        @click="openPicker($event, week, 'saturday', key)"
                                        @keydown.enter.space.prevent="openPicker($event, week, 'saturday', key)"
                                    >
                                        <!-- Saving spinner -->
                                        <span
                                            v-if="isSaving(week.id, 'saturday', key)"
                                            class="absolute inset-0 flex items-center justify-center bg-white/70 z-10"
                                        >
                                            <svg class="w-4 h-4 text-violet-500 animate-spin" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
                                            </svg>
                                        </span>

                                        <!-- Cell content -->
                                        <div v-if="week.meetings?.saturday?.[key]?.attendant" class="pr-4">
                                            <span class="block text-sm font-semibold text-gray-900 leading-tight">
                                                {{ week.meetings.saturday[key].attendant.name }}
                                            </span>
                                            <span
                                                :class="['inline-block mt-1 px-1.5 py-0.5 rounded-full font-medium text-[10px] leading-none', roleBadgeClass(week.meetings.saturday[key].attendant.role)]"
                                            >
                                                {{ roleAbbr(week.meetings.saturday[key].attendant.role) }}
                                            </span>
                                        </div>
                                        <span v-else class="text-gray-300 font-medium select-none">&mdash;</span>

                                        <!-- Pencil hint icon -->
                                        <span class="absolute top-2 right-2 opacity-0 group-hover/cell:opacity-100 transition-opacity">
                                            <svg class="w-3 h-3 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 13l6.586-6.586a2 2 0 112.828 2.828L11.828 15.828A2 2 0 019 15v-2z" />
                                            </svg>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Table footer -->
            <div class="px-4 md:px-6 py-3 bg-gray-50 border-t border-gray-100 text-xs text-gray-400 flex flex-wrap items-center justify-between gap-2">
                <span>{{ schedule.name }} &mdash; Programa de Acomodadores</span>
                <span>{{ totalAssignments }} de {{ totalSlots }} asignaciones completadas</span>
            </div>

            <!-- Participation counter -->
            <div class="border-t border-gray-100 px-6 py-4">
                <div class="flex items-center gap-2 mb-3">
                    <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide">Participación</span>
                </div>
                <div class="flex flex-wrap gap-1.5">
                    <div
                        v-for="att in sortedParticipation"
                        :key="att.id"
                        class="inline-flex items-center gap-1.5 pl-1 pr-2 py-1 rounded-full border border-gray-100 bg-gray-50 text-xs"
                        :title="`${att.name} — ${att.count} asignación${att.count !== 1 ? 'es' : ''}`"
                    >
                        <span :class="['w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold shrink-0', countBadgeClass(att.count)]">
                            {{ att.count }}
                        </span>
                        <span class="font-medium text-gray-700 leading-none">{{ att.name }}</span>
                    </div>
                </div>
            </div>
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
                    <div class="relative z-10 bg-white rounded-none sm:rounded-2xl shadow-2xl flex flex-col w-full max-w-5xl h-[100dvh] sm:h-[90vh] pb-safe sm:pb-0">
                        <!-- Header -->
                        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 shrink-0">
                            <div>
                                <h2 class="text-base font-semibold text-gray-900">{{ schedule.name }}</h2>
                                <p class="text-xs text-gray-400 mt-0.5">Programa de Acomodadores</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a
                                    :href="`/schedules/${schedule.id}/pdf`"
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
                            :src="`/schedules/${schedule.id}/pdf?inline=1`"
                            class="flex-1 w-full rounded-b-2xl"
                            style="border: none;"
                            title="Vista previa PDF"
                        />
                    </div>
                </div>
            </Transition>
        </Teleport>

    </AppLayout>
</template>

<script setup>
import { ref, computed, nextTick, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

// ── Props ────────────────────────────────────────────────────────────────────
const props = defineProps({
    schedule:   { type: Object, required: true },
    weeks:      { type: Array,  default: () => [] },
    roles:      { type: Object, default: () => ({}) },
    attendants: { type: Array,  default: () => [] },
});

// ── PDF preview modal ─────────────────────────────────────────────────────────
const pdfPreviewOpen = ref(false);

// ── Local mutable copy of weeks ──────────────────────────────────────────────
const localWeeks = ref(JSON.parse(JSON.stringify(props.weeks)));

// ── CSRF token ───────────────────────────────────────────────────────────────
const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// ── Saving state set ─────────────────────────────────────────────────────────
// Key format: "weekId:day:roleKey"
const savingCells = ref(new Set());

function savingKey(weekId, day, roleKey) {
    return `${weekId}:${day}:${roleKey}`;
}
function isSaving(weekId, day, roleKey) {
    return savingCells.value.has(savingKey(weekId, day, roleKey));
}

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

// ── Picker state ─────────────────────────────────────────────────────────────
const pickerEl       = ref(null);
const pickerSearchEl = ref(null);
const pickerSearch   = ref('');

const picker = ref({
    open:               false,
    sheet:              false,  // bottom-sheet mode on mobile
    weekId:             null,
    day:                null,   // 'friday' | 'saturday'
    roleKey:            null,
    assignmentId:       null,
    currentAttendantId: null,
    x:                  0,
    y:                  0,
});

function isActivePicker(weekId, day, roleKey) {
    return (
        picker.value.open &&
        picker.value.weekId   === weekId &&
        picker.value.day      === day    &&
        picker.value.roleKey  === roleKey
    );
}

// Attendants already assigned to OTHER roles in the same meeting (same week + day)
const meetingOccupiedIds = computed(() => {
    if (!picker.value.open) return new Set();
    const week = localWeeks.value.find(w => w.id === picker.value.weekId);
    if (!week) return new Set();
    const meeting = week.meetings?.[picker.value.day];
    if (!meeting) return new Set();

    const occupied = new Set();
    for (const [rKey, assignment] of Object.entries(meeting)) {
        if (rKey === picker.value.roleKey) continue; // skip current role
        if (assignment?.attendant?.id != null) {
            occupied.add(assignment.attendant.id);
        }
    }
    return occupied;
});

const filteredAttendants = computed(() => {
    const search = pickerSearch.value.trim().toLowerCase();
    return props.attendants
        .filter(a => !search || a.name.toLowerCase().includes(search))
        .map(a => ({
            ...a,
            _isCurrent:  a.id === picker.value.currentAttendantId,
            _inMeeting:  meetingOccupiedIds.value.has(a.id),
        }));
});

// ── Open / close picker ───────────────────────────────────────────────────────
function openPicker(event, week, day, roleKey) {
    const assignment = week.meetings?.[day]?.[roleKey] ?? null;

    // Bottom sheet on mobile, positioned popover on desktop
    const sheet = window.innerWidth < 640;

    // Position the picker below (or above) the clicked cell
    const cell = event.currentTarget;
    const rect = cell.getBoundingClientRect();
    const pickerHeight = 340; // approximate
    const pickerWidth  = 280;

    let x = rect.left;
    let y = rect.bottom + 6;

    // Clamp horizontally so it doesn't overflow off the right edge
    if (x + pickerWidth > window.innerWidth - 8) {
        x = window.innerWidth - pickerWidth - 8;
    }
    // Flip upward if near bottom of viewport
    if (y + pickerHeight > window.innerHeight - 8) {
        y = rect.top - pickerHeight - 6;
        if (y < 8) y = 8;
    }

    picker.value = {
        open:               true,
        sheet,
        weekId:             week.id,
        day,
        roleKey,
        assignmentId:       assignment?.id ?? null,
        currentAttendantId: assignment?.attendant?.id ?? null,
        x,
        y,
    };

    pickerSearch.value = '';

    // Attach outside-click listener
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
        if (pdfPreviewOpen.value) {
            pdfPreviewOpen.value = false;
        } else {
            closePicker();
        }
    }
}

onUnmounted(() => {
    document.removeEventListener('click', handleOutsideClick, { capture: true });
    document.removeEventListener('keydown', handlePickerKeydown);
});

// ── Assignment handler ────────────────────────────────────────────────────────
async function handleAssign(attendantId) {
    const { weekId, day, roleKey, assignmentId } = picker.value;

    if (!assignmentId) {
        showToast('No se encontró el registro de asignación para esta celda.', 'error');
        closePicker();
        return;
    }

    // Snapshot for revert
    const week = localWeeks.value.find(w => w.id === weekId);
    if (!week) { closePicker(); return; }

    const previousAttendant = week.meetings?.[day]?.[roleKey]?.attendant
        ? { ...week.meetings[day][roleKey].attendant }
        : null;

    // --- Optimistic update ---
    const newAttendant = attendantId !== null
        ? (props.attendants.find(a => a.id === attendantId) ?? null)
        : null;

    if (week.meetings?.[day]?.[roleKey]) {
        week.meetings[day][roleKey].attendant = newAttendant
            ? { id: newAttendant.id, name: newAttendant.name, role: newAttendant.role }
            : null;
    }

    closePicker();

    // Mark cell as saving
    const key = savingKey(weekId, day, roleKey);
    savingCells.value = new Set([...savingCells.value, key]);

    try {
        const response = await fetch(`/schedule-assignments/${assignmentId}`, {
            method:  'PATCH',
            headers: {
                'Content-Type':  'application/json',
                'Accept':        'application/json',
                'X-CSRF-TOKEN':  csrf,
            },
            body: JSON.stringify({ attendant_id: attendantId }),
        });

        const data = await response.json();

        if (response.status === 422) {
            // Revert
            if (week.meetings?.[day]?.[roleKey]) {
                week.meetings[day][roleKey].attendant = previousAttendant;
            }
            const msg = data?.errors?.attendant_id?.[0]
                ?? 'Esta persona ya está asignada a otro cargo en esta reunión.';
            showToast(msg, 'error');
            return;
        }

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        // Confirm server state (the attendant object might differ slightly)
        if (week.meetings?.[day]?.[roleKey]) {
            week.meetings[day][roleKey].attendant = data?.attendant ?? null;
        }

    } catch (err) {
        // Revert on network error
        if (week.meetings?.[day]?.[roleKey]) {
            week.meetings[day][roleKey].attendant = previousAttendant;
        }
        showToast('No se pudo guardar. Por favor intenta de nuevo.', 'error');
    } finally {
        const next = new Set(savingCells.value);
        next.delete(key);
        savingCells.value = next;
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

// ── Role display helpers ──────────────────────────────────────────────────────
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

function roleLabel(key) {
    return props.roles[key] ?? key;
}

// ── Stats ─────────────────────────────────────────────────────────────────────
const roleKeys = computed(() => Object.keys(props.roles));

const totalSlots = computed(() => localWeeks.value.length * 2 * roleKeys.value.length);

const totalAssignments = computed(() => {
    let count = 0;
    for (const week of localWeeks.value) {
        for (const day of ['friday', 'saturday']) {
            const meeting = week.meetings?.[day];
            if (!meeting) continue;
            for (const key of roleKeys.value) {
                if (meeting[key]?.attendant) count++;
            }
        }
    }
    return count;
});

// ── Participation counter ──────────────────────────────────────────────────────
const assignmentCounts = computed(() => {
    const counts = {};
    for (const week of localWeeks.value) {
        for (const day of ['friday', 'saturday']) {
            const meeting = week.meetings?.[day];
            if (!meeting) continue;
            for (const key of roleKeys.value) {
                const att = meeting[key]?.attendant;
                if (att) counts[att.id] = (counts[att.id] ?? 0) + 1;
            }
        }
    }
    return counts;
});

const sortedParticipation = computed(() =>
    props.attendants
        .map(att => ({ ...att, count: assignmentCounts.value[att.id] ?? 0 }))
        .sort((a, b) => a.count - b.count || a.name.localeCompare(b.name))
);

const minParticipationCount = computed(() => {
    const assigned = sortedParticipation.value.filter(a => a.count > 0).map(a => a.count);
    return assigned.length > 0 ? Math.min(...assigned) : 0;
});

function countBadgeClass(count) {
    if (count === 0) return 'bg-gray-100 text-gray-500';
    const diff = count - minParticipationCount.value;
    if (diff === 0) return 'bg-green-100 text-green-700';
    if (diff === 1) return 'bg-amber-100 text-amber-700';
    return 'bg-red-100 text-red-700';
}
</script>
