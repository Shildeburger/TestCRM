<script setup>
import { ref, onMounted } from "vue";
import { useForm, Link } from "@inertiajs/vue3";

const props = defineProps({
    contact: Object,
    chat: Object,
    messages: Array,
});

const messages = ref(props.messages ?? []);

const form = useForm({
    telegram_user_id: props.contact.telegram_user_id || "",
    telegram_username: props.contact.telegram_username || "",
});

const submit = () => {
    form.patch(route("contacts.telegram.link", props.contact.id));
};

onMounted(() => {
    if (!props.chat) {
        return;
    }
    const chatID = props.chat.id;

    window.Echo.private(`telegram.chat.${chatID}`).listen(
        ".TelegramMessageCreated",
        (e) => {
            messages.value.push({
                id: e.id,
                telegram_chat_id: e.chat_id,
                text: e.text,
                direction: e.direction,
                from_role: e.from_role,
                sent_at: e.sent_at,
            });
        },
    );
});
</script>

<template>
    <div class="p-6 space-y-6">
        <header class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold">{{ contact.name }}</h1>
                <p class="text-sm text-gray-500">
                    {{ contact.email }} · {{ contact.phone }}
                </p>
            </div>

            <Link :href="route('contacts.index')" class="text-blue-500 text-sm">
                ← Назад к списку
            </Link>
        </header>

        <div class="messages">
            <div v-for="message in messages" :key="message.id">
                <span>{{ message.from_role }}:</span>
                <span>{{ message.text }}</span>
            </div>
        </div>

        <section class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-2">
                <h2 class="font-semibold">Общая информация</h2>
                <p>Компания: {{ contact.company || "—" }}</p>
                <p>Должность: {{ contact.job_title || "—" }}</p>
                <p>Страна: {{ contact.country || "—" }}</p>
            </div>

            <div class="space-y-3">
                <h2 class="font-semibold">Telegram</h2>

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
                        class="px-3 py-1 text-sm bg-blue-600 text-white rounded disabled:opacity-50"
                    >
                        Привязать Telegram
                    </button>
                </form>
            </div>
        </section>
    </div>
</template>
