<template>
    <div class="min-h-screen">
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 w-64 bg-[#1b1b1b] border-r border-[#2a2a2a] flex flex-col">
            <div class="px-6 py-5 border-b border-[#2a2a2a] flex items-center gap-3">
                <div class="w-9 h-9 shrink-0 rounded-md bg-indigo-600 flex items-center justify-center text-[#151515] font-extrabold text-base tracking-tight">A</div>
                <div>
                    <h1 class="text-white font-bold text-lg tracking-tight">Asignaly</h1>
                    <p class="text-gray-400 text-xs mt-0.5">Panel de Administración</p>
                </div>
            </div>

            <nav class="flex-1 py-6 px-4 space-y-1">
                <Link
                    href="/admin/congregations"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 text-sm font-medium transition-colors',
                        isActive('/admin/congregations')
                            ? 'bg-[#2c2c2c] text-white shadow-[inset_3px_0_0_0_#e08316]'
                            : 'text-gray-500 hover:bg-[#232323] hover:text-white',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                    Congregaciones
                </Link>

                <Link
                    href="/admin/users"
                    :class="[
                        'flex items-center gap-3 px-3 py-2.5 text-sm font-medium transition-colors',
                        isActive('/admin/users')
                            ? 'bg-[#2c2c2c] text-white shadow-[inset_3px_0_0_0_#e08316]'
                            : 'text-gray-500 hover:bg-[#232323] hover:text-white',
                    ]"
                >
                    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                    </svg>
                    Usuarios
                </Link>
            </nav>

            <div class="border-t border-[#2a2a2a] px-4 py-4">
                <div class="flex items-center justify-between gap-2">
                    <div class="min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ $page.props.auth.user?.name }}</p>
                        <p class="text-gray-400 text-xs mt-0.5">Super Admin</p>
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
            </div>
        </aside>

        <!-- Main content -->
        <div class="ml-64 flex flex-col min-h-screen">
            <header class="bg-[#111111] border-b border-[#262626] px-8 py-4">
                <h2 class="text-lg font-semibold text-white">{{ title }}</h2>
            </header>

            <div v-if="$page.props.flash?.success" class="mx-8 mt-4 bg-green-50 border border-green-200 text-green-800 text-sm px-4 py-3 rounded-lg">
                {{ $page.props.flash.success }}
            </div>
            <div v-if="$page.props.flash?.error" class="mx-8 mt-4 bg-red-50 border border-red-200 text-red-800 text-sm px-4 py-3 rounded-lg">
                {{ $page.props.flash.error }}
            </div>

            <main class="flex-1 p-8">
                <slot />
            </main>
        </div>
    </div>
</template>

<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: 'Administración' },
});

const page = usePage();

function logout() {
    router.post('/logout');
}

function isActive(path) {
    return page.url.startsWith(path);
}
</script>
