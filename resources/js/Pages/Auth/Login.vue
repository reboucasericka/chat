<script setup>
import Checkbox from "@/Components/Checkbox.vue";
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: "",
    password: "",
    remember: false,
});

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="Login" />
    <div
        class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-8"
    >
        <div class="w-full max-w-md">
            <div class="mb-5 text-center">
                <h1 class="text-2xl font-semibold text-gray-900">Chat</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Acesse sua conta para continuar.
                </p>
            </div>

            <div
                class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
            >
                <div
                    v-if="status"
                    class="mb-4 text-sm font-medium text-emerald-600"
                >
                    {{ status }}
                </div>

                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel
                            for="email"
                            value="Email"
                            class="text-gray-700"
                        />
                        <TextInput
                            id="email"
                            v-model="form.email"
                            type="email"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                            required
                            autofocus
                            autocomplete="username"
                        />
                        <InputError class="mt-2" :message="form.errors.email" />
                    </div>

                    <div>
                        <InputLabel
                            for="password"
                            value="Senha"
                            class="text-gray-700"
                        />
                        <TextInput
                            id="password"
                            v-model="form.password"
                            type="password"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                            required
                            autocomplete="current-password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>

                    <div class="block">
                        <label class="flex items-center">
                            <Checkbox
                                name="remember"
                                v-model:checked="form.remember"
                            />
                            <span class="ms-2 text-sm text-gray-600"
                                >Lembrar de mim</span
                            >
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-md bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Entrando...</span>
                        <span v-else>Entrar</span>
                    </button>

                    <div class="mt-4 space-y-2 text-center">
                        <Link
                            v-if="canResetPassword"
                            :href="route('password.request')"
                            class="block text-sm text-gray-600 hover:text-blue-600"
                        >
                            Esqueceu a senha?
                        </Link>
                        <Link
                            :href="route('register')"
                            class="block text-sm text-gray-600 hover:text-blue-600"
                        >
                            Criar conta
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
