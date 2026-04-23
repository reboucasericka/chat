<script setup>
import data from '@emoji-mart/data';
import { Picker } from 'emoji-mart';
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const props = defineProps({
    sending: { type: Boolean, default: false },
});

const emit = defineEmits(['send']);

const body = ref('');
const fileInput = ref(null);
const selectedFile = ref(null);
const previewUrl = ref(null);
const isDragging = ref(false);
const showEmojiPicker = ref(false);
const pickerContainer = ref(null);

function openFilePicker() {
    fileInput.value?.click();
}

function setSelectedFile(file) {
    if (!file) {
        return;
    }

    selectedFile.value = file;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = file.type.startsWith('image/') ? URL.createObjectURL(file) : null;
}

function handleFile(event) {
    const file = event.target.files?.[0] ?? null;
    setSelectedFile(file);
}

function removeFile() {
    selectedFile.value = null;

    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    previewUrl.value = null;

    if (fileInput.value) {
        fileInput.value.value = '';
    }
}

function submit() {
    if (props.sending) {
        return;
    }

    if (!body.value.trim() && !selectedFile.value) {
        return;
    }

    const formData = new FormData();
    if (body.value.trim()) {
        formData.append('body', body.value.trim());
    }
    if (selectedFile.value) {
        formData.append('attachment', selectedFile.value);
        formData.append('file', selectedFile.value);
    }

    emit('send', formData);

    body.value = '';
    showEmojiPicker.value = false;
    removeFile();
}

function onKeydown(event) {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        submit();
    }
}

function onDragOver(event) {
    event.preventDefault();
    isDragging.value = true;
}

function onDragLeave(event) {
    event.preventDefault();
    isDragging.value = false;
}

function onDrop(event) {
    event.preventDefault();
    isDragging.value = false;

    const file = event.dataTransfer?.files?.[0] ?? null;
    setSelectedFile(file);
}

function toggleEmojiPicker() {
    showEmojiPicker.value = !showEmojiPicker.value;
}

function onEmojiSelect(emoji) {
    body.value += emoji.native;
    showEmojiPicker.value = false;
}

function mountPicker() {
    if (!pickerContainer.value) {
        return;
    }

    pickerContainer.value.innerHTML = '';

    const picker = new Picker({
        data,
        theme: 'light',
        onEmojiSelect,
    });

    pickerContainer.value.appendChild(picker);
}

function onDocumentClick(event) {
    if (!event.target.closest('.composer-wrapper')) {
        showEmojiPicker.value = false;
    }
}

watch(showEmojiPicker, async (isVisible) => {
    if (!isVisible) {
        if (pickerContainer.value) {
            pickerContainer.value.innerHTML = '';
        }
        return;
    }

    await nextTick();
    if (!pickerContainer.value) {
        return;
    }

    mountPicker();
});

onBeforeUnmount(() => {
    if (previewUrl.value) {
        URL.revokeObjectURL(previewUrl.value);
    }

    document.removeEventListener('click', onDocumentClick);
});

onMounted(() => {
    document.addEventListener('click', onDocumentClick);
});
</script>

<template>
    <div class="composer-wrapper relative w-full overflow-visible">
        <form
            class="sticky bottom-0 border-t border-gray-200 bg-white px-4 py-3"
            :class="{ 'bg-blue-50': isDragging }"
            @submit.prevent="submit"
            @dragover="onDragOver"
            @dragleave="onDragLeave"
            @drop="onDrop"
        >
            <input
                ref="fileInput"
                type="file"
                class="hidden"
                @change="handleFile"
            >

        <div
            v-if="selectedFile"
            class="mb-2 flex items-center gap-3"
        >
            <div
                v-if="previewUrl"
                class="h-16 w-16 overflow-hidden rounded-md border border-gray-200"
            >
                <img
                    :src="previewUrl"
                    class="h-full w-full object-cover"
                    alt="Preview"
                >
            </div>
            <div class="min-w-0 flex-1 truncate text-xs text-gray-500">
                {{ selectedFile.name }}
            </div>
            <button
                type="button"
                class="text-xs text-red-500 hover:text-red-600"
                @click="removeFile"
            >
                remover
            </button>
        </div>

        <p
            v-else-if="isDragging"
            class="mb-2 text-xs text-blue-600"
        >
            Solte o ficheiro para anexar
        </p>

            <div
                v-if="showEmojiPicker"
                class="absolute bottom-full left-0 z-50 mb-2"
            >
                <div
                    ref="pickerContainer"
                    class="max-h-[350px] overflow-hidden rounded-xl border border-gray-200 bg-white shadow-lg"
                />
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-full text-gray-500 hover:bg-gray-100"
                    aria-label="Anexo"
                    @click="openFilePicker"
                >
                    +
                </button>

                <button
                    type="button"
                    class="px-2 text-gray-500 hover:text-gray-700"
                    aria-label="Emojis"
                    @click="toggleEmojiPicker"
                >
                    😊
                </button>

                <textarea
                    v-model="body"
                    rows="2"
                    class="min-h-[42px] flex-1 resize-none rounded-full border-0 bg-gray-100 px-4 py-2 text-sm text-gray-900 focus:ring-2 focus:ring-blue-500"
                    placeholder="Escreva uma mensagem..."
                    @keydown="onKeydown"
                />

                <button
                    type="submit"
                    class="inline-flex items-center gap-2 rounded-full bg-blue-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-600 disabled:cursor-not-allowed disabled:opacity-70"
                    :disabled="sending"
                >
                    <span
                        v-if="sending"
                        class="h-3.5 w-3.5 animate-spin rounded-full border-2 border-white/80 border-t-transparent"
                    />
                    {{ sending ? 'Enviando...' : 'Enviar' }}
                </button>
            </div>
        </form>
    </div>
</template>
