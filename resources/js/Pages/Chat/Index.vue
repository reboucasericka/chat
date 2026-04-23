<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import ChatShell from "@/Components/chat/ChatShell.vue";
</script>

<template>
    <AuthenticatedLayout>
        <div class="py-6">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <ChatShell />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
