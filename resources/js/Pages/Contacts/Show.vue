<script setup>
import { useForm, Link } from "@inertiajs/vue3";
import ChatWindow from "@/Components/ChatWindow.vue";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const props = defineProps({
    contact: Object,
    chat: Object,
    messages: Array,
});

const form = useForm({
    telegram_user_id: props.contact.telegram_user_id || "",
    telegram_username: props.contact.telegram_username || "",
});

const submit = () => {
    form.patch(route("contacts.telegram.link", props.contact.id));
};
</script>

<template>
    <AuthLayout>
        <div class="p-6 space-y-6">
            <header class="flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">{{ contact.name }}</h1>
                    <p class="text-sm text-gray-500">
                        {{ contact.email }} · {{ contact.phone }}
                    </p>
                </div>

                <Link
                    :href="route('contacts.index')"
                    class="text-blue-500 text-sm"
                >
                    ← Назад к списку
                </Link>
            </header>

            <section class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="space-y-6 lg:col-span-1">
                    <div class="space-y-2 p-4 border rounded-lg bg-white">
                        <h2 class="font-semibold text-lg border-b pb-2 mb-3">
                            Общая информация
                        </h2>
                        <p class="text-sm">
                            <span class="text-gray-500">Компания:</span>
                            {{ contact.company || "—" }}
                        </p>
                        <p class="text-sm">
                            <span class="text-gray-500">Должность:</span>
                            {{ contact.job_title || "—" }}
                        </p>
                        <p class="text-sm">
                            <span class="text-gray-500">Страна:</span>
                            {{ contact.country || "—" }}
                        </p>
                    </div>

                    <div class="space-y-3 p-4 border rounded-lg bg-white">
                        <h2 class="font-semibold text-lg border-b pb-2 mb-3">
                            Telegram
                        </h2>

                        <form @submit.prevent="submit" class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium"
                                    >Telegram user ID</label
                                >
                                <input
                                    type="number"
                                    v-model="form.telegram_user_id"
                                    class="mt-1 w-full border rounded px-2 py-1 text-sm"
                                />
                                <p
                                    v-if="form.errors.telegram_user_id"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.telegram_user_id }}
                                </p>
                            </div>

                            <div>
                                <label class="block text-sm font-medium"
                                    >Telegram username</label
                                >
                                <input
                                    type="text"
                                    v-model="form.telegram_username"
                                    class="mt-1 w-full border rounded px-2 py-1 text-sm"
                                />
                                <p
                                    v-if="form.errors.telegram_username"
                                    class="text-xs text-red-500"
                                >
                                    {{ form.errors.telegram_username }}
                                </p>
                            </div>

                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded disabled:opacity-50"
                            >
                                Привязать Telegram
                            </button>
                        </form>
                    </div>
                </div>

                <div class="lg:col-span-2">
                    <ChatWindow :chat="chat" :initial-messages="messages" />
                </div>
            </section>
        </div>
    </AuthLayout>
</template>
