<script setup>
import { onMounted, ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import chatService from '@/services/chatService';

const props = defineProps({
    open: { type: Boolean, default: false },
    selected: { type: Array, default: () => [] },
});

const emit = defineEmits(['close', 'apply']);

const users = ref([]);
const selectedIds = ref([]);
const page = usePage();

watch(
    () => props.selected,
    (value) => {
        selectedIds.value = [...value];
    },
    { immediate: true },
);

function toggleUser(userId) {
    if (selectedIds.value.includes(userId)) {
        selectedIds.value = selectedIds.value.filter((id) => id !== userId);
        return;
    }

    selectedIds.value.push(userId);
}

onMounted(async () => {
    if (!page.props.auth?.user) {
        users.value = [];
        return;
    }

    try {
        const response = await chatService.users();
        users.value = response.data.data ?? [];
    } catch (error) {
        console.error('User picker error', error);
        users.value = [];
    }
});
</script>

<template>
    <Modal
        :show="open"
        @close="emit('close')"
    >
        <div class="p-6">
            <h3 class="mb-3 text-sm font-semibold text-gray-800">Selecionar membros</h3>

            <ul class="max-h-72 space-y-1 overflow-y-auto">
                <li
                    v-for="user in users"
                    :key="user.id"
                >
                    <label class="flex cursor-pointer items-center gap-2 rounded px-2 py-1 hover:bg-gray-100">
                        <input
                            type="checkbox"
                            :checked="selectedIds.includes(user.id)"
                            @change="toggleUser(user.id)"
                        >
                        <span class="text-sm text-gray-700">{{ user.name }}</span>
                    </label>
                </li>
            </ul>

            <div class="mt-4 flex justify-end gap-2">
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
                    @click="emit('apply', selectedIds); emit('close')"
                >
                    Aplicar
                </button>
            </div>
        </div>
    </Modal>
</template>
