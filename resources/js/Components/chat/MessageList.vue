<script setup>
import { nextTick, watch } from 'vue';
import MessageItem from '@/components/chat/MessageItem.vue';

const emit = defineEmits(['reaction-changed']);

const props = defineProps({
    messages: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
});

watch(
    () => props.messages,
    async () => {
        await nextTick();
        const box = document.getElementById('chat-message-list');
        if (box) {
            box.scrollTop = box.scrollHeight;
        }
    },
    { deep: true },
);
</script>

<template>
    <div
        id="chat-message-list"
        class="min-h-0 flex-1 overflow-y-auto bg-white"
    >
        <div class="mx-auto max-w-3xl space-y-4 px-6 py-6">
            <p
                v-if="loading"
                class="text-sm text-gray-500"
            >
                Carregando...
            </p>

            <MessageItem
                v-for="message in messages"
                :key="message.id"
                :message="message"
                @reaction-changed="emit('reaction-changed')"
            />
        </div>
    </div>
</template>
