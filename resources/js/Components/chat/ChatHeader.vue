<script setup>
defineProps({
    title: { type: String, required: true },
    subtitle: { type: String, default: '' },
});
</script>

<template>
    <header class="border-b border-gray-200 bg-white px-6 py-3">
        <h1 class="truncate text-lg font-semibold text-gray-900">{{ title }}</h1>
        <div v-if="subtitle" class="mt-0.5 flex items-center gap-1.5 text-xs text-gray-500">
            <span
                class="h-1.5 w-1.5 rounded-full"
                :class="subtitle === 'online' ? 'bg-green-500' : 'bg-gray-400'"
            />
            <span>{{ subtitle }}</span>
        </div>
    </header>
</template>
