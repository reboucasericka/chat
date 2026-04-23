<script setup>
defineProps({
    title: { type: String, default: 'Nenhuma conversa selecionada' },
    description: { type: String, default: 'Escolha uma sala ou conversa direta' },
});
</script>

<template>
    <div class="flex h-full flex-1 items-center justify-center p-8 text-center">
        <div class="max-w-sm">
            <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-full bg-gray-100 text-gray-400">
                💬
            </div>
            <h3 class="text-base font-medium text-gray-400">{{ title }}</h3>
            <p class="mt-2 text-sm text-gray-400">{{ description }}</p>
        </div>
    </div>
</template>
