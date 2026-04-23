<script setup>
import InputError from "@/Components/InputError.vue";
import InputLabel from "@/Components/InputLabel.vue";
import TextInput from "@/Components/TextInput.vue";
import { Head, Link, useForm } from "@inertiajs/vue3";

const form = useForm({
    name: "",
    email: "",
    password: "",
    password_confirmation: "",
});

const submit = () => {
    form.post(route("register"), {
        onFinish: () => form.reset("password", "password_confirmation"),
    });
};
</script>

<template>
    <Head title="Criar conta" />

    <div
        class="flex min-h-screen items-center justify-center bg-gray-100 px-4 py-8"
    >
        <div class="w-full max-w-md">
            <div class="mb-5 text-center">
                <h1 class="text-2xl font-semibold text-gray-900">
                    Criar conta no Chat
                </h1>
                <p class="mt-1 text-sm text-gray-500">
                    Preencha os dados para começar.
                </p>
            </div>

            <div
                class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm"
            >
                <form @submit.prevent="submit" class="space-y-4">
                    <div>
                        <InputLabel
                            for="name"
                            value="Nome"
                            class="text-gray-700"
                        />
                        <TextInput
                            id="name"
                            type="text"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                            v-model="form.name"
                            required
                            autofocus
                            autocomplete="name"
                        />
                        <InputError class="mt-2" :message="form.errors.name" />
                    </div>

                    <div>
                        <InputLabel
                            for="email"
                            value="Email"
                            class="text-gray-700"
                        />
                        <TextInput
                            id="email"
                            type="email"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                            v-model="form.email"
                            required
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
                            type="password"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                            v-model="form.password"
                            required
                            autocomplete="new-password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password"
                        />
                    </div>

                    <div>
                        <InputLabel
                            for="password_confirmation"
                            value="Confirmar senha"
                            class="text-gray-700"
                        />
                        <TextInput
                            id="password_confirmation"
                            type="password"
                            class="mt-1 block w-full rounded-md border border-gray-300 bg-white text-gray-900 focus:border-blue-500 focus:ring-blue-500"
                            v-model="form.password_confirmation"
                            required
                            autocomplete="new-password"
                        />
                        <InputError
                            class="mt-2"
                            :message="form.errors.password_confirmation"
                        />
                    </div>

                    <button
                        type="submit"
                        class="w-full rounded-md bg-blue-500 px-4 py-2 text-sm font-semibold text-white hover:bg-blue-600 disabled:opacity-60"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Criando...</span>
                        <span v-else>Criar conta</span>
                    </button>

                    <div class="text-center">
                        <Link
                            :href="route('login')"
                            class="text-sm text-gray-600 hover:text-blue-600"
                        >
                            Já tenho conta
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
