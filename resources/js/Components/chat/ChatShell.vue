<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import chatService from '@/services/chatService';
import ChatHeader from '@/components/chat/ChatHeader.vue';
import ChatSidebar from '@/components/chat/ChatSidebar.vue';
import ChatWindow from '@/components/chat/ChatWindow.vue';
import EmptyState from '@/components/chat/EmptyState.vue';

const items = ref([]);
const active = ref(null);
const messages = ref([]);
const loading = ref(false);
const isSending = ref(false);
const joining = ref(false);
let presenceInterval = null;
let pollingInterval = null;
const page = usePage();

const currentTitle = computed(() => active.value?.title ?? 'Conversa');
const currentSubtitle = computed(() => {
    if (!active.value) {
        return '';
    }

    if (active.value.kind === 'room') {
        return active.value.is_member ? 'membros • online' : 'sala • offline';
    }

    return active.value.counterpart_is_online ? 'conversa direta • online' : 'conversa direta • offline';
});
const needsJoin = computed(() => active.value?.kind === 'room' && active.value?.is_member === false);
const hasConversations = computed(() => items.value.length > 0);
const hasAnyRoom = computed(() => items.value.some((item) => item.kind === 'room'));
const emptyTitle = computed(() => (hasConversations.value ? 'Selecione uma conversa' : 'Nenhuma conversa disponível'));
const emptyDescription = computed(() => (hasConversations.value
    ? 'Escolha uma sala ou uma conversa direta na barra lateral.'
    : 'Ainda não existem salas ou conversas diretas para este utilizador.'));

function pickDefaultConversation(list) {
    const firstRoom = list.find((item) => item.kind === 'room');
    return firstRoom ?? list[0] ?? null;
}

async function loadSidebar() {
    try {
        const response = await chatService.sidebar();
        items.value = response.data.data ?? [];

        if (!active.value && items.value.length > 0) {
            const defaultItem = pickDefaultConversation(items.value);
            if (defaultItem) {
                await selectConversation(defaultItem);
            }
            return;
        }

        if (active.value) {
            const updatedActive = items.value.find(
                (item) => item.id === active.value.id && item.kind === active.value.kind,
            );

            if (updatedActive) {
                active.value = updatedActive;
            } else if (items.value.length > 0) {
                const defaultItem = pickDefaultConversation(items.value);
                if (defaultItem) {
                    await selectConversation(defaultItem);
                }
            } else {
                active.value = null;
                messages.value = [];
                loading.value = false;
            }
        }
    } catch (error) {
        console.error('Sidebar error', error);
        items.value = [];
        active.value = null;
        messages.value = [];
    }
}

async function handleRoomCreated(room) {
    await loadSidebar();

    if (!room?.id) {
        return;
    }

    const createdRoom = items.value.find((item) => item.kind === 'room' && item.id === room.id);
    if (createdRoom) {
        await selectConversation(createdRoom);
    }
}

async function selectConversation(item) {
    active.value = item;

    if (item.kind === 'room' && item.is_member === false) {
        messages.value = [];
        loading.value = false;
        return;
    }

    loading.value = true;

    try {
        if (item.kind === 'room') {
            const response = await chatService.roomMessages(item.id);
            messages.value = response.data.data ?? [];
            await chatService.markRoomRead(item.id);
        } else {
            const response = await chatService.directMessages(item.id);
            messages.value = response.data.data ?? [];
            await chatService.markDirectRead(item.id);
        }
    } finally {
        loading.value = false;
    }
}

async function joinActiveRoom() {
    if (!active.value || active.value.kind !== 'room' || joining.value) {
        return;
    }

    joining.value = true;

    try {
        await chatService.joinRoom(active.value.id);
        await loadSidebar();

        const joinedRoom = items.value.find((item) => item.kind === 'room' && item.id === active.value.id);
        if (joinedRoom) {
            await selectConversation(joinedRoom);
        }
    } finally {
        joining.value = false;
    }
}

async function sendMessage(payload) {
    if (!active.value || isSending.value) {
        return;
    }

    isSending.value = true;

    try {
        if (active.value.kind === 'room') {
            await chatService.sendRoomMessage(active.value.id, payload);
        } else {
            const body = payload instanceof FormData ? payload.get('body') : payload?.body;
            if (!body) {
                return;
            }

            await chatService.sendDirectMessage(active.value.id, { body });
        }

        await loadSidebar();
        const refreshedActive = items.value.find((item) => item.id === active.value?.id && item.kind === active.value?.kind);
        if (refreshedActive) {
            await selectConversation(refreshedActive);
        }
    } finally {
        isSending.value = false;
    }
}

async function refreshMessages() {
    if (!active.value) {
        return;
    }

    try {
        if (active.value.kind === 'room') {
            const response = await chatService.roomMessages(active.value.id);
            messages.value = response.data.data ?? [];
            return;
        }

        const response = await chatService.directMessages(active.value.id);
        messages.value = response.data.data ?? [];
    } catch (error) {
        console.error('Erro ao atualizar mensagens', error);
    }
}

async function startDirectConversation(userId) {
    const response = await chatService.openDirect(userId);
    const conversationId = response.data?.data?.id;
    await loadSidebar();

    const createdDirect = items.value.find((item) => item.kind === 'direct' && item.id === conversationId);
    if (createdDirect) {
        await selectConversation(createdDirect);
    }
}

async function deleteConversation(item) {
    const confirmed = window.confirm('Apagar conversa?');
    if (!confirmed) {
        return;
    }

    try {
        if (item.kind === 'room') {
            await chatService.deleteRoom(item.id);
        } else {
            await chatService.deleteDirect(item.id);
        }

        await loadSidebar();
        active.value = null;
        messages.value = [];
    } catch (error) {
        console.error('Erro ao apagar conversa', error);
    }
}

watch(
    () => page.props.auth?.user,
    async (user) => {
        if (!user) {
            if (presenceInterval) {
                window.clearInterval(presenceInterval);
                presenceInterval = null;
            }
            items.value = [];
            active.value = null;
            messages.value = [];
            return;
        }

        await loadSidebar();

        if (presenceInterval) {
            window.clearInterval(presenceInterval);
        }

        presenceInterval = window.setInterval(() => {
            chatService.pingPresence();
        }, 30000);
    },
    { immediate: true },
);

onMounted(() => {
    pollingInterval = window.setInterval(refreshMessages, 3000);
});

onBeforeUnmount(() => {
    if (presenceInterval) {
        window.clearInterval(presenceInterval);
    }
    if (pollingInterval) {
        window.clearInterval(pollingInterval);
    }
});
</script>

<template>
    <div class="grid h-[calc(100vh-13rem)] grid-cols-[16rem_1fr] overflow-hidden bg-white text-gray-900">
        <ChatSidebar
            :items="items"
            :active-id="active?.id"
            :active-kind="active?.kind"
            @select="selectConversation"
            @refresh="loadSidebar"
            @created="handleRoomCreated"
            @start-direct="startDirectConversation"
            @delete-conversation="deleteConversation"
        />

        <div class="flex min-h-0 flex-col">
            <ChatHeader :title="currentTitle" :subtitle="currentSubtitle" />

            <ChatWindow
                v-if="active && !needsJoin"
                :messages="messages"
                :loading="loading"
                :sending="isSending"
                @send="sendMessage"
                @reaction-changed="refreshMessages"
            />

            <div v-else-if="active && needsJoin" class="grid flex-1 place-items-center p-8">
                <div class="space-y-3 text-center">
                    <p class="text-sm text-gray-600">Voce ainda nao participa desta sala.</p>
                    <button
                        type="button"
                        class="rounded-md bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-500"
                        :disabled="joining"
                        :class="{ 'cursor-not-allowed opacity-60': joining }"
                        @click="joinActiveRoom"
                    >
                        {{ joining ? 'Entrando...' : 'Entrar na sala' }}
                    </button>
                </div>
            </div>

            <EmptyState
                v-else
                :title="emptyTitle"
                :description="emptyDescription"
            />
        </div>
    </div>
</template>
