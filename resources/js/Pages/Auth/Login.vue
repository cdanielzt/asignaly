<template>
    <div class="min-h-screen bg-gray-100 flex items-center justify-center p-4">
        <div class="w-full max-w-sm">

            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">

                <!-- Header -->
                <div class="bg-indigo-900 px-8 py-8 text-center">
                    <div class="w-12 h-12 bg-indigo-700 rounded-xl flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                    </div>
                    <h1 class="text-white font-bold text-2xl tracking-tight">Asignaly</h1>
                    <p class="text-indigo-300 text-sm mt-1">Herramientas del Salón del Reino</p>
                </div>

                <!-- Form -->
                <form class="px-8 py-8 space-y-5" @submit.prevent="submit">

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Correo electrónico</label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            autocomplete="email"
                            autofocus
                            required
                            :class="[
                                'w-full px-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition-shadow',
                                form.errors.email
                                    ? 'border-red-400 focus:ring-red-400'
                                    : 'border-gray-300 focus:ring-indigo-500',
                            ]"
                        />
                        <p v-if="form.errors.email" class="mt-1.5 text-xs text-red-600">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Contraseña</label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            autocomplete="current-password"
                            required
                            :class="[
                                'w-full px-3.5 py-2.5 text-sm border rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition-shadow',
                                form.errors.password
                                    ? 'border-red-400 focus:ring-red-400'
                                    : 'border-gray-300 focus:ring-indigo-500',
                            ]"
                        />
                        <p v-if="form.errors.password" class="mt-1.5 text-xs text-red-600">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <div class="flex items-center gap-2">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="w-4 h-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        />
                        <label for="remember" class="text-sm text-gray-600 select-none">Recordarme</label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-semibold py-2.5 rounded-lg transition-colors text-sm"
                    >
                        {{ form.processing ? 'Iniciando sesión…' : 'Iniciar sesión' }}
                    </button>

                </form>
            </div>

        </div>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email:    '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>
