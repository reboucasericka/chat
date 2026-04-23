<script setup>
defineProps({
    title: { type: String, required: true },
    items: { type: Array, default: () => [] },
    activeId: { type: Number, default: null },
    activeKind: { type: String, default: null },
    emptyMessage: { type: String, default: 'Sem itens' },
});

const emit = defineEmits(['select', 'delete']);
</script>

<template>
    <section>
        <h4
            v-if="title"
            class="mb-1 px-4 text-[11px] font-semibold uppercase tracking-wide text-gray-400"
        >
            {{ title }}
        </h4>

        <ul class="space-y-0.5">
            <li
                v-if="items.length === 0"
                class="rounded-md px-4 py-3 text-xs text-gray-500"
            >
                {{ emptyMessage }}
            </li>

            <li
                v-for="item in items"
                :key="`${item.kind}-${item.id}`"
            >
                <div
                    class="group border-l-4 border-transparent px-4 py-3 text-gray-800 transition-colors"
                    :class="activeId === item.id && activeKind === item.kind
                        ? 'border-blue-500 bg-white font-semibold'
                        : 'hover:bg-gray-200'"
                >
                    <div class="flex items-start justify-between gap-2">
                        <button
                            type="button"
                            class="min-w-0 flex-1 text-left"
                            @click="emit('select', item)"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <div class="flex min-w-0 items-center gap-2">
                                    <span
                                        v-if="item.kind === 'direct'"
                                        class="inline-block h-2 w-2 rounded-full"
                                        :class="item.counterpart_is_online ? 'bg-green-500' : 'bg-gray-300'"
                                    />
                                    <p class="truncate text-sm font-bold text-gray-800">{{ item.title }}</p>
                                </div>
                                <span
                                    v-if="item.kind === 'room' && !item.is_member"
                                    class="rounded-full bg-gray-300 px-2 py-0.5 text-[11px] text-gray-700"
                                >
                                    🔒 Entrar
                                </span>
                                <span
                                    v-else-if="item.unread_count"
                                    class="rounded-full bg-gray-300 px-2 py-0.5 text-[11px] text-gray-700"
                                >
                                    {{ item.unread_count }}
                                </span>
                            </div>
                            <p class="truncate text-sm text-gray-500">{{ item.snippet || 'Sem mensagens' }}</p>
                        </button>
                        <button
                            type="button"
                            class="rounded px-1 text-gray-400 opacity-0 transition hover:bg-gray-300 hover:text-gray-700 group-hover:opacity-100"
                            aria-label="Apagar conversa"
                            title="Apagar conversa"
                            @click.stop="emit('delete', item)"
                        >
                            🗑️
                        </button>
                    </div>
                </div>
            </li>
        </ul>
    </section>
</template>
