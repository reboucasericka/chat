<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps({
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Recuperar senha" />

        <div class="mb-4 text-sm text-gray-600">
            Esqueceu a sua senha? Informe o seu e-mail para receber um link de
            redefinição.
        </div>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4 flex items-center justify-end">
                <button
                    type="submit"
                    class="rounded-md bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600 disabled:opacity-60"
                    :disabled="form.processing"
                >
                    Enviar link de redefinição
                </button>
            </div>
        </form>
    </GuestLayout>
</template>
