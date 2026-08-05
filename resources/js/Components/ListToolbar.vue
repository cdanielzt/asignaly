<template>
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <!-- Search -->
        <div class="relative flex-1 min-w-0">
            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M17 11A6 6 0 115 11a6 6 0 0112 0z" />
            </svg>
            <input
                :value="search"
                type="search"
                :placeholder="searchPlaceholder"
                class="w-full pl-9 pr-3 py-2.5 text-sm bg-white border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent placeholder-gray-400"
                @input="$emit('update:search', $event.target.value)"
            />
        </div>

        <div class="flex gap-2">
            <!-- Filter -->
            <select
                v-if="filterOptions.length"
                :value="filter"
                class="flex-1 sm:flex-initial bg-white border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent cursor-pointer min-w-0"
                @change="$emit('update:filter', $event.target.value)"
            >
                <option value="">{{ filterAllLabel }}</option>
                <option v-for="o in filterOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
            </select>

            <!-- Sort -->
            <select
                :value="sort"
                class="flex-1 sm:flex-initial bg-white border border-gray-200 rounded-lg px-3 py-2.5 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent cursor-pointer min-w-0"
                @change="$emit('update:sort', $event.target.value)"
            >
                <option v-for="o in sortOptions" :key="o.value" :value="o.value">{{ o.label }}</option>
            </select>
        </div>
    </div>
</template>

<script setup>
defineProps({
    search:            { type: String, default: '' },
    filter:            { type: String, default: '' },
    sort:              { type: String, default: '' },
    searchPlaceholder: { type: String, default: 'Buscar…' },
    filterAllLabel:    { type: String, default: 'Todos' },
    filterOptions:     { type: Array,  default: () => [] },
    sortOptions:       { type: Array,  default: () => [] },
});

defineEmits(['update:search', 'update:filter', 'update:sort']);
</script>
