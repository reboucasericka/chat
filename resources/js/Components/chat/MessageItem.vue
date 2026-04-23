<script setup>
import { computed } from 'vue';
import { usePage } from '@inertiajs/vue3';
import chatService from '@/services/chatService';

const props = defineProps({
    message: { type: Object, required: true },
});

const emit = defineEmits(['reaction-changed']);

const page = usePage();
const isMine = computed(() => props.message.sender?.id === page.props.auth?.user?.id);
const isImageAttachment = computed(() => {
    const path = props.message.attachment_path ?? '';
    return /\.(jpg|jpeg|png|gif|webp)$/i.test(path);
});

const reactionList = computed(() => props.message.reactions ?? []);
const quickEmojis = ['👍', '❤️', '😮'];

function initials(name) {
    return (name ?? '?')
        .split(' ')
        .map((part) => part[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
}

async function toggleReaction(emoji) {
    const r = reactionList.value.find((x) => x.emoji === emoji);
    try {
        if (r?.reacted) {
            await chatService.removeMessageReaction(props.message.id, emoji);
        } else {
            await chatService.addMessageReaction(props.message.id, emoji);
        }
        emit('reaction-changed');
    } catch (error) {
        console.error('Reação', error);
    }
}
</script>

<template>
    <article class="group flex items-end gap-3" :class="isMine ? 'justify-end' : 'justify-start'">
        <div
            v-if="!isMine"
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-gray-200 text-[10px] font-semibold text-gray-700"
        >
            {{ initials(props.message.sender?.name) }}
        </div>

        <div class="max-w-md">
            <div class="mb-1 text-xs text-gray-500" :class="isMine ? 'text-right' : 'text-left'">
                {{ `${props.message.sender?.name || 'Usuario'} • ${new Date(props.message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}` }}
            </div>

            <div
                class="mb-0.5 flex min-h-6 items-end transition-opacity"
                :class="[isMine ? 'justify-end' : 'justify-start', 'opacity-0 group-hover:opacity-100']"
            >
                <div
                    class="inline-flex items-center gap-0.5 rounded-md bg-white/90 px-1 py-0.5 shadow-sm ring-1 ring-gray-200"
                >
                    <button
                        v-for="e in quickEmojis"
                        :key="e"
                        type="button"
                        class="rounded px-1.5 text-sm leading-none hover:bg-gray-100"
                        :title="`Reagir com ${e}`"
                        @click.stop="toggleReaction(e)"
                    >
                        {{ e }}
                    </button>
                </div>
            </div>

            <div
                class="rounded-2xl px-4 py-2 text-sm leading-relaxed break-words shadow-sm"
                :class="isMine ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-900'"
            >
                <p class="whitespace-pre-wrap break-words">{{ props.message.body }}</p>
            </div>

            <div
                v-if="props.message.attachment_path"
                class="mt-2"
            >
                <img
                    v-if="isImageAttachment"
                    :src="`/storage/${props.message.attachment_path}`"
                    class="max-w-xs rounded-md border border-gray-200"
                    alt="Anexo"
                >
                <a
                    v-else
                    :href="`/storage/${props.message.attachment_path}`"
                    target="_blank"
                    class="text-sm underline"
                    :class="isMine ? 'text-blue-100' : 'text-blue-600'"
                >
                    Download ficheiro
                </a>
            </div>

            <div
                v-if="reactionList.length"
                class="mt-1 flex flex-wrap gap-1"
            >
                <button
                    v-for="r in reactionList"
                    :key="r.emoji"
                    type="button"
                    class="rounded bg-gray-200 px-2 text-xs text-gray-800 hover:bg-gray-300"
                    :class="{ 'ring-1 ring-blue-500': r.reacted }"
                    @click.stop="toggleReaction(r.emoji)"
                >
                    {{ r.emoji }} {{ r.count }}
                </button>
            </div>
        </div>

        <div
            v-if="isMine"
            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-blue-100 text-[10px] font-semibold text-blue-700"
        >
            {{ initials(props.message.sender?.name) }}
        </div>
    </article>
</template>
