<script setup>
import MessageList from '@/components/chat/MessageList.vue';
import MessageComposer from '@/components/chat/MessageComposer.vue';

defineProps({
    messages: { type: Array, default: () => [] },
    loading: { type: Boolean, default: false },
    sending: { type: Boolean, default: false },
});

const emit = defineEmits(['send', 'reaction-changed']);
</script>

<template>
    <div class="flex min-h-0 flex-1 flex-col">
        <MessageList
            :messages="messages"
            :loading="loading"
            @reaction-changed="emit('reaction-changed')"
        />

        <MessageComposer :sending="sending" @send="emit('send', $event)" />
    </div>
</template>
