<script setup>
import { computed, watch, ref } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import chatService from '@/services/chatService';

const props = defineProps({
    open: { type: Boolean, default: false },
});

const emit = defineEmits(['close', 'created']);

const name = ref('');
const avatar = ref('');
const userIds = ref([]);
const users = ref([]);
const errorMessage = ref('');
const page = usePage();
const selectedCountLabel = computed(() => `${userIds.value.length} utilizadores selecionados`);
const isCreateDisabled = computed(() => !name.value.trim());

function toggleUser(id) {
    if (userIds.value.includes(id)) {
        userIds.value = userIds.value.filter((item) => item !== id);
        return;
    }

    userIds.value.push(id);
}

async function loadUsers() {
    if (!page.props.auth?.user) {
        users.value = [];
        return;
    }

    try {
        const response = await chatService.users();
        const currentUserId = page.props.auth.user.id;
        users.value = (response.data.data ?? []).filter((user) => user.id !== currentUserId);
    } catch {
        users.value = [];
    }
}

async function createRoom() {
    if (!name.value.trim()) {
        return;
    }

    if (page.props.auth?.user?.role !== 'admin') {
        errorMessage.value = 'Apenas admins podem criar salas.';
        return;
    }

    errorMessage.value = '';

    let response;

    try {
        response = await chatService.createRoom({
            name: name.value.trim(),
            avatar: avatar.value.trim() || null,
            user_ids: userIds.value,
        });
    } catch (error) {
        errorMessage.value = error.response?.status === 403
            ? 'Apenas admins podem criar salas.'
            : 'Nao foi possivel criar a sala. Tente novamente.';
        return;
    }

    name.value = '';
    avatar.value = '';
    userIds.value = [];
    emit('created', response.data.data);
    emit('close');
}

watch(
    () => props.open,
    (isOpen) => {
        if (isOpen) {
            loadUsers();
        }
    },
    { immediate: true },
);
</script>

<template>
    <Modal
        :show="open"
        @close="emit('close')"
    >
        <div class="space-y-4 p-6">
            <h3 class="text-base font-semibold text-gray-900">Criar sala</h3>

            <input
                v-model="name"
                type="text"
                class="w-full rounded-md border-gray-300 text-sm"
                placeholder="Nome da sala"
            >

            <input
                v-model="avatar"
                type="text"
                class="w-full rounded-md border-gray-300 text-sm"
                placeholder="Avatar da sala (URL opcional)"
            >

            <div class="space-y-2">
                <p class="text-sm font-medium text-gray-700">Selecionar utilizadores</p>
                <p class="text-xs text-gray-500">{{ selectedCountLabel }}</p>
                <div class="max-h-44 overflow-y-auto rounded-md border border-gray-200 p-2">
                    <label
                        v-for="user in users"
                        :key="user.id"
                        class="flex cursor-pointer items-center gap-2 rounded px-2 py-1 hover:bg-gray-100"
                    >
                        <input
                            type="checkbox"
                            :checked="userIds.includes(user.id)"
                            @change="toggleUser(user.id)"
                        >
                        <span class="text-sm text-gray-700">{{ user.name }}</span>
                    </label>
                </div>
            </div>

            <p v-if="errorMessage" class="text-sm text-red-600">
                {{ errorMessage }}
            </p>

            <div class="flex justify-end gap-2">
                <button
                    type="button"
                    class="rounded-md border border-gray-300 px-3 py-1.5 text-sm"
                    @click="emit('close')"
                >
                    Cancelar
                </button>
                <button
                    type="button"
                    class="rounded-md bg-gray-900 px-3 py-1.5 text-sm text-white"
                    :disabled="isCreateDisabled"
                    :class="{ 'cursor-not-allowed opacity-50': isCreateDisabled }"
                    @click="createRoom"
                >
                    Criar
                </button>
            </div>
        </div>
    </Modal>
</template>
