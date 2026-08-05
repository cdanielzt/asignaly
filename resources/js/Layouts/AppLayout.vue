<template>
    <div class="min-h-screen">

        <!-- Sidebar (desktop only) -->
        <aside
            :class="[
                'fixed inset-y-0 left-0 bg-[#1b1b1b] border-r border-[#2a2a2a] hidden lg:flex flex-col transition-all duration-300 overflow-hidden',
                collapsed ? 'w-16' : 'w-64',
            ]"
        >
            <!-- Header -->
            <div
                :class="[
                    'flex items-center border-b border-[#2a2a2a] shrink-0 transition-all duration-300',
                    collapsed ? 'justify-center px-3 py-5' : 'justify-between px-6 py-5',
                ]"
            >
                <div v-if="!collapsed" class="flex items-center gap-3 min-w-0">
                    <div class="w-9 h-9 shrink-0 rounded-md bg-indigo-600 flex items-center justify-center text-[#151515] font-extrabold text-base tracking-tight">A</div>
                    <div class="min-w-0">
                        <h1 class="text-white font-bold text-lg tracking-tight whitespace-nowrap">Asignaly</h1>
                        <p class="text-gray-400 text-xs mt-0.5 truncate max-w-[140px]">
                            {{ $page.props.auth.user?.congregation?.name ?? 'Herramientas del Salón del Reino' }}
                        </p>
                    </div>
                </div>

                <button
                    type="button"
                    class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-[#2a2a2a] transition-colors shrink-0"
                    :title="collapsed ? 'Expandir barra lateral' : 'Contraer barra lateral'"
                    @click="toggleSidebar"
                >
                    <!-- Chevrons pointing right when collapsed, left when expanded -->
                    <svg v-if="collapsed" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 5l7 7-7 7M5 5l7 7-7 7" />
                    </svg>
                    <svg v-else class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11 19l-7-7 7-7m8 14l-7-7 7-7" />
                    </svg>
                </button>
            </div>

            <!-- Nav -->
            <nav :class="['flex-1 py-6 space-y-1', collapsed ? 'px-2' : 'px-4']">
                <div v-if="!collapsed" class="mb-1 px-3">
                    <p class="text-gray-400 text-[10px] uppercase tracking-widest font-semibold">Congregación</p>
                </div>
                <Link
                    href="/"
                    :class="[
                        'flex items-center text-sm font-medium transition-colors',
                        collapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2.5',
                        isActive('/') ? 'bg-[#2c2c2c] text-white shadow-[inset_3px_0_0_0_#e08316]' : 'text-gray-500 hover:bg-[#232323] hover:text-white',
                    ]"
                    :title="collapsed ? 'Inicio' : undefined"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span v-if="!collapsed">Inicio</span>
                </Link>

                <Link
                    href="/attendants"
                    :class="[
                        'flex items-center text-sm font-medium transition-colors',
                        collapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2.5',
                        isActive('/attendants') ? 'bg-[#2c2c2c] text-white shadow-[inset_3px_0_0_0_#e08316]' : 'text-gray-500 hover:bg-[#232323] hover:text-white',
                    ]"
                    :title="collapsed ? 'Hermanos' : undefined"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    <span v-if="!collapsed">Hermanos</span>
                </Link>

                <Link
                    href="/schedules"
                    :class="[
                        'flex items-center text-sm font-medium transition-colors',
                        collapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2.5',
                        isActive('/schedules') ? 'bg-[#2c2c2c] text-white shadow-[inset_3px_0_0_0_#e08316]' : 'text-gray-500 hover:bg-[#232323] hover:text-white',
                    ]"
                    :title="collapsed ? 'Acomodadores' : undefined"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span v-if="!collapsed">Acomodadores</span>
                </Link>

                <Link
                    href="/students"
                    :class="[
                        'flex items-center text-sm font-medium transition-colors',
                        collapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2.5',
                        isActive('/students') ? 'bg-[#2c2c2c] text-white shadow-[inset_3px_0_0_0_#e08316]' : 'text-gray-500 hover:bg-[#232323] hover:text-white',
                    ]"
                    :title="collapsed ? 'Estudiantes' : undefined"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.422L12 14z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 14L3.84 9.578M12 14v7M3.84 9.578L3 9.27v4.93a2 2 0 001.106 1.789l.894.447" />
                    </svg>
                    <span v-if="!collapsed">Estudiantes</span>
                </Link>

                <Link
                    href="/meetings"
                    :class="[
                        'flex items-center text-sm font-medium transition-colors',
                        collapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2.5',
                        isActive('/meetings') ? 'bg-[#2c2c2c] text-white shadow-[inset_3px_0_0_0_#e08316]' : 'text-gray-500 hover:bg-[#232323] hover:text-white',
                    ]"
                    :title="collapsed ? 'Reuniones' : undefined"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-9 0h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2zm2-13v2m6-2v2" />
                    </svg>
                    <span v-if="!collapsed">Reuniones</span>
                </Link>

                <!-- Congregation admin links -->
                <template v-if="isCongregationAdmin">
                    <div v-if="!collapsed" class="mt-4 mb-1 px-3">
                        <p class="text-gray-400 text-[10px] uppercase tracking-widest font-semibold">Administración</p>
                    </div>
                    <Link
                        href="/congregation/settings"
                        :class="[
                            'flex items-center text-sm font-medium transition-colors',
                            collapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2.5',
                            isActive('/congregation/settings') ? 'bg-[#2c2c2c] text-white shadow-[inset_3px_0_0_0_#e08316]' : 'text-gray-500 hover:bg-[#232323] hover:text-white',
                        ]"
                        :title="collapsed ? 'Configuración' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        <span v-if="!collapsed">Configuración</span>
                    </Link>

                    <Link
                        href="/congregation/users"
                        :class="[
                            'flex items-center text-sm font-medium transition-colors',
                            collapsed ? 'justify-center px-2 py-2.5' : 'gap-3 px-3 py-2.5',
                            isActive('/congregation/users') ? 'bg-[#2c2c2c] text-white shadow-[inset_3px_0_0_0_#e08316]' : 'text-gray-500 hover:bg-[#232323] hover:text-white',
                        ]"
                        :title="collapsed ? 'Usuarios' : undefined"
                    >
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                        <span v-if="!collapsed">Usuarios</span>
                    </Link>
                </template>
            </nav>

            <!-- User + logout -->
            <div :class="['border-t border-[#2a2a2a] shrink-0', collapsed ? 'px-2 py-3' : 'px-4 py-4']">
                <div v-if="!collapsed" class="flex items-center justify-between gap-2 mb-0">
                    <div class="min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ $page.props.auth.user?.name }}</p>
                        <p class="text-gray-400 text-xs mt-0.5 whitespace-nowrap">
                            {{ isCongregationAdmin ? 'Admin' : 'Miembro' }}
                        </p>
                    </div>
                    <form method="POST" action="/logout" @submit.prevent="logout">
                        <button
                            type="submit"
                            title="Cerrar sesión"
                            class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-[#2a2a2a] transition-colors shrink-0"
                        >
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                        </button>
                    </form>
                </div>

                <form v-if="collapsed" method="POST" action="/logout" class="flex justify-center" @submit.prevent="logout">
                    <button
                        type="submit"
                        title="Cerrar sesión"
                        class="w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-white hover:bg-[#2a2a2a] transition-colors"
                    >
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main content -->
        <div
            :class="[
                'flex flex-col min-h-screen transition-all duration-300 pb-20 lg:pb-0',
                collapsed ? 'lg:ml-16' : 'lg:ml-64',
            ]"
        >
            <!-- Admin congregation-view banner -->
            <div
                v-if="$page.props.adminViewingCongregation"
                class="bg-amber-50 border-b border-amber-200 px-4 lg:px-6 py-2.5 flex flex-wrap items-center justify-between gap-x-4 gap-y-2"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-xs font-semibold text-amber-700 uppercase tracking-wide shrink-0">Administrando:</span>
                    <select
                        :value="$page.props.auth.user?.congregation?.id"
                        class="text-sm text-amber-900 bg-amber-100 border border-amber-300 rounded-md px-2 py-1 focus:outline-none focus:ring-2 focus:ring-amber-400 min-w-0 max-w-[220px] truncate"
                        @change="switchCongregation"
                    >
                        <option
                            v-for="c in $page.props.allCongregations"
                            :key="c.id"
                            :value="c.id"
                        >
                            {{ c.name }}
                        </option>
                    </select>
                </div>
                <button
                    type="button"
                    class="shrink-0 inline-flex items-center gap-1.5 text-xs font-medium text-amber-700 hover:text-amber-900 bg-amber-100 hover:bg-amber-200 border border-amber-300 rounded-md px-3 py-1.5 transition-colors"
                    @click="switchBack"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver al panel admin
                </button>
            </div>

            <!-- Impersonation banner -->
            <div
                v-if="$page.props.impersonating"
                class="bg-indigo-50 border-b border-indigo-200 px-4 lg:px-6 py-2.5 flex flex-wrap items-center justify-between gap-x-4 gap-y-2"
            >
                <div class="flex items-center gap-3 min-w-0">
                    <span class="text-xs font-semibold text-indigo-700 uppercase tracking-wide shrink-0">Suplantando a:</span>
                    <span class="text-sm text-gray-900 font-semibold truncate">{{ $page.props.auth.user?.name }}</span>
                </div>
                <button
                    type="button"
                    class="shrink-0 inline-flex items-center gap-1.5 text-xs font-medium text-indigo-700 hover:text-indigo-900 bg-indigo-100 hover:bg-indigo-200 border border-indigo-300 rounded-md px-3 py-1.5 transition-colors"
                    @click="stopImpersonating"
                >
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Volver a mi cuenta
                </button>
            </div>

            <header class="sticky top-0 z-30 lg:static bg-[#111111]/95 backdrop-blur lg:bg-[#111111] border-b border-[#262626] px-4 lg:px-8 py-4 pt-safe flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white truncate">{{ title }}</h2>
            </header>

            <div v-if="$page.props.flash?.success" class="mx-4 lg:mx-8 mt-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mx-4 lg:mx-8 mt-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
                {{ $page.props.flash.error }}
            </div>

            <main class="flex-1 p-4 lg:p-8">
                <slot />
            </main>
        </div>

        <!-- Mobile bottom tab bar -->
        <nav class="lg:hidden fixed bottom-0 inset-x-0 z-40 bg-[#1b1b1b]/95 backdrop-blur border-t border-[#2a2a2a] pb-safe">
            <div class="grid grid-cols-5">
                <Link
                    v-for="tab in tabs"
                    :key="tab.href"
                    :href="tab.href"
                    class="flex flex-col items-center justify-center gap-0.5 py-2 min-h-[52px] transition-colors active:bg-[#232323]"
                    :class="isActive(tab.href) && !moreOpen ? 'text-white' : 'text-gray-500'"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" :stroke-width="isActive(tab.href) && !moreOpen ? 2.2 : 1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" />
                    </svg>
                    <span class="text-[10px] font-medium leading-none">{{ tab.label }}</span>
                </Link>

                <button
                    type="button"
                    class="flex flex-col items-center justify-center gap-0.5 py-2 min-h-[52px] transition-colors active:bg-[#232323]"
                    :class="moreOpen || moreIsActive ? 'text-white' : 'text-gray-500'"
                    @click="moreOpen = !moreOpen"
                >
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" :stroke-width="moreOpen || moreIsActive ? 2.2 : 1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span class="text-[10px] font-medium leading-none">Más</span>
                </button>
            </div>
        </nav>

        <!-- "Más" bottom sheet -->
        <Teleport to="body">
            <Transition name="sheet">
                <div v-if="moreOpen" class="lg:hidden fixed inset-0 z-50" @click.self="moreOpen = false">
                    <div class="absolute inset-0 bg-black/50" @click="moreOpen = false" />
                    <div class="absolute bottom-0 inset-x-0 bg-[#1b1b1b] border-t border-[#2a2a2a] rounded-t-2xl pb-safe">
                        <div class="mx-auto mt-2.5 mb-1 h-1 w-9 rounded-full bg-[#3a3a3a]"></div>

                        <!-- User card -->
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-[#2a2a2a]">
                            <div class="w-10 h-10 shrink-0 rounded-full bg-indigo-600 flex items-center justify-center text-white font-bold text-base">
                                {{ ($page.props.auth.user?.name ?? '?').charAt(0).toUpperCase() }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-white text-base font-medium truncate">{{ $page.props.auth.user?.name }}</p>
                                <p class="text-gray-400 text-sm truncate">
                                    {{ isCongregationAdmin ? 'Admin' : 'Miembro' }}
                                    <template v-if="$page.props.auth.user?.congregation"> · {{ $page.props.auth.user.congregation.name }}</template>
                                </p>
                            </div>
                        </div>

                        <div class="py-2">
                            <Link
                                href="/students"
                                class="flex items-center gap-3 px-5 py-3 text-base font-medium transition-colors active:bg-[#232323]"
                                :class="isActive('/students') ? 'text-white' : 'text-gray-400'"
                                @click="moreOpen = false"
                            >
                                <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l9-5-9-5-9 5 9 5z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 14l6.16-3.422A12.083 12.083 0 0112 21a12.083 12.083 0 01-6.16-10.422L12 14z" />
                                </svg>
                                Estudiantes
                            </Link>

                            <template v-if="isCongregationAdmin">
                                <Link
                                    href="/congregation/settings"
                                    class="flex items-center gap-3 px-5 py-3 text-base font-medium transition-colors active:bg-[#232323]"
                                    :class="isActive('/congregation/settings') ? 'text-white' : 'text-gray-400'"
                                    @click="moreOpen = false"
                                >
                                    <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Configuración
                                </Link>
                                <Link
                                    href="/congregation/users"
                                    class="flex items-center gap-3 px-5 py-3 text-base font-medium transition-colors active:bg-[#232323]"
                                    :class="isActive('/congregation/users') ? 'text-white' : 'text-gray-400'"
                                    @click="moreOpen = false"
                                >
                                    <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                    Usuarios
                                </Link>
                            </template>

                            <div class="my-2 border-t border-[#2a2a2a]"></div>

                            <button
                                type="button"
                                class="w-full flex items-center gap-3 px-5 py-3 text-base font-medium text-red-400 transition-colors active:bg-[#232323]"
                                @click="logout"
                            >
                                <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                </svg>
                                Cerrar sesión
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        default: 'Inicio',
    },
});

const page = usePage();

const collapsed = ref(false);
const moreOpen = ref(false);

const isCongregationAdmin = computed(() => page.props.auth.user?.role === 'congregation_admin');

// Bottom tab bar (mobile). Estudiantes + admin links live in the "Más" sheet.
const tabs = [
    { href: '/',            label: 'Inicio',       icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
    { href: '/attendants',  label: 'Hermanos',     icon: 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z' },
    { href: '/schedules',   label: 'Acomodadores', icon: 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z' },
    { href: '/meetings',    label: 'Reuniones',    icon: 'M9 17v-2a2 2 0 012-2h2a2 2 0 012 2v2m-9 0h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v8a2 2 0 002 2zm2-13v2m6-2v2' },
];

const moreIsActive = computed(() =>
    ['/students', '/congregation'].some(p => page.url.startsWith(p))
);

// Close the sheet after any Inertia navigation (e.g. back button)
const offNavigate = router.on('navigate', () => { moreOpen.value = false; });
onUnmounted(offNavigate);

onMounted(() => {
    const saved = localStorage.getItem('sidebar-collapsed');
    if (saved !== null) collapsed.value = saved === 'true';
});

function toggleSidebar() {
    collapsed.value = !collapsed.value;
    localStorage.setItem('sidebar-collapsed', String(collapsed.value));
}

function logout() {
    router.post('/logout');
}

function switchBack() {
    router.post('/admin/switch-back');
}

function switchCongregation(event) {
    const congregationId = event.target.value;
    router.post(`/admin/congregations/${congregationId}/switch`);
}

function stopImpersonating() {
    router.post('/impersonate/stop');
}

function isActive(path) {
    if (path === '/') return page.url === '/';
    return page.url.startsWith(path);
}
</script>

<style scoped>
.sheet-enter-active,
.sheet-leave-active {
    transition: opacity 0.2s ease;
}
.sheet-enter-active > div:last-child,
.sheet-leave-active > div:last-child {
    transition: transform 0.25s ease;
}
.sheet-enter-from,
.sheet-leave-to {
    opacity: 0;
}
.sheet-enter-from > div:last-child,
.sheet-leave-to > div:last-child {
    transform: translateY(100%);
}
</style>
