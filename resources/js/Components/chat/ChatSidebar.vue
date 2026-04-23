<script setup>
import { computed, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import SidebarSection from '@/components/chat/SidebarSection.vue';
import RoomCreateModal from '@/components/chat/RoomCreateModal.vue';
import chatService from '@/services/chatService';

const props = defineProps({
    items: { type: Array, default: () => [] },
    activeId: { type: Number, default: null },
    activeKind: { type: String, default: null },
});

const emit = defineEmits(['select', 'refresh', 'created', 'startDirect', 'deleteConversation']);
const creatingRoom = ref(false);
const creatingDirect = ref(false);
const directUsers = ref([]);
const page = usePage();

const rooms = computed(() => props.items.filter((item) => item.kind === 'room'));
const directs = computed(() => props.items.filter((item) => item.kind === 'direct'));
const conversations = computed(() => [...rooms.value, ...directs.value]);
const isAdmin = computed(() => page.props.auth?.user?.role === 'admin');

async function openDirectPicker() {
    try {
        const response = await chatService.users();
        directUsers.value = response.data.data ?? [];
        creatingDirect.value = true;
    } catch {
        directUsers.value = [];
    }
}
</script>

<template>
    <aside class="flex min-h-0 w-[16rem] flex-col border-r border-gray-200 bg-gray-100">
        <div class="flex items-center justify-between px-4 py-3">
            <h3 class="text-xs font-semibold uppercase tracking-wide text-gray-500">Conversas</h3>
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    class="text-xs font-medium text-gray-700 hover:text-gray-900"
                    @click="openDirectPicker"
                >
                    Nova direta
                </button>
                <button
                    v-if="isAdmin"
                    type="button"
                    class="text-xs font-medium text-gray-700 hover:text-gray-900"
                    @click="creatingRoom = true"
                >
                    Nova sala
                </button>
            </div>
        </div>

        <div class="min-h-0 flex-1 overflow-y-auto py-2">
            <SidebarSection
                title=""
                :items="conversations"
                :active-id="activeId"
                :active-kind="activeKind"
                empty-message="Nenhuma conversa disponível"
                @select="emit('select', $event)"
                @delete="emit('deleteConversation', $event)"
            />
        </div>

        <RoomCreateModal
            v-if="isAdmin"
            :open="creatingRoom"
            @close="creatingRoom = false"
            @created="emit('refresh'); emit('created', $event)"
        />

        <div
            v-if="creatingDirect"
            class="fixed inset-0 z-50 grid place-items-center bg-black/30 p-4"
        >
            <div class="w-full max-w-md rounded-lg bg-white p-4 shadow-lg">
                <div class="mb-3 flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-gray-900">Iniciar conversa direta</h4>
                    <button type="button" class="text-sm text-gray-500" @click="creatingDirect = false">Fechar</button>
                </div>
                <ul class="max-h-72 space-y-1 overflow-y-auto">
                    <li v-for="user in directUsers" :key="user.id">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between rounded-md px-2 py-2 text-left hover:bg-gray-100"
                            @click="creatingDirect = false; emit('startDirect', user.id)"
                        >
                            <span class="text-sm text-gray-800">{{ user.name }}</span>
                            <span class="text-xs" :class="user.is_online ? 'text-green-600' : 'text-gray-400'">
                                {{ user.is_online ? 'online' : 'offline' }}
                            </span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </aside>
</template>
