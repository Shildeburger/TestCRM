<script setup>
import { ref, onMounted, onUnmounted, nextTick } from "vue";
import axios from "axios";

const props = defineProps({
    contactId: {
        type: Number,
        required: true,
    },
    chat: Object,
    initialMessages: {
        type: Array,
        default: () => [],
    },
});

const messages = ref([...props.initialMessages]);
const messagesContainer = ref(null);
const newMessageText = ref("");

const scrollToBottom = async () => {
    await nextTick();
    if (messagesContainer.value) {
        messagesContainer.value.scrollTop =
            messagesContainer.value.scrollHeight;
    }
};

onMounted(() => {
    console.log("Пытаемся подключиться к чату. Объект chat:", props.chat);
    scrollToBottom();
    if (props.chat && props.chat.id) {
        console.log("ID чата:", props.chat.id);
        window.Echo.private(`telegram.chat.${props.chat.id}`).listen(
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
                scrollToBottom;
            },
        );
    }
});

onUnmounted(() => {
    if (props.chat && props.chat.id) {
        window.Echo.leave(`telegram.chat.${props.chat.id}`);
    }
});

const sendMessage = async () => {
    if (!newMessageText.value.trim() || !props.chat) return;

    //Сохранени текста в переменную и очищение инпута
    const textToSend = newMessageText.value;
    newMessageText.value = "";

    try {
        // Отправляем запрос на наш маршрут в ContactController
        const response = await axios.post(
            route("contacts.messages.store", props.contactId),
            {
                text: textToSend,
            },
        );

        //Получаем сохраненное сообщение от сервера и отправляем его в массив
        messages.value.push(response.data);

        //Прокрутка чата в низ
        scrollToBottom();
    } catch (error) {
        console.error("Ошибка отправки:", error);
        // Если произошла ошибка сети или Telegram заблокировал бота,
        // возвращаем текст обратно в инпут, чтобы не потерять его
        newMessageText.value = textToSend;
        alert("Не удалось отправить сообщение. Проверьте консоль.");
    }
};
</script>

<template>
    <div class="flex flex-col bg-white border rounded-lg shadow-sm h-[500px]">
        <div class="px-4 py-3 border-b bg-gray-50 rounded-t-lg">
            <h3 class="font-semibold text-gray-700">История переписки</h3>
        </div>

        <div
            ref="messagesContainer"
            class="flex-1 p-4 overflow-y-auto space-y-4 bg-gray-50/50"
        >
            <div
                v-if="messages.length === 0"
                class="text-center text-gray-400 mt-10"
            >
                Сообщений пока нет.
            </div>

            <div
                v-for="message in messages"
                :key="message.id"
                class="flex"
                :class="
                    message.direction === 'incoming'
                        ? 'justify-start'
                        : 'justify-end'
                "
            >
                <div
                    class="max-w-[75%] rounded-2xl px-4 py-2 text-sm"
                    :class="
                        message.direction === 'incoming'
                            ? 'bg-white border text-gray-800 rounded-bl-none shadow-sm'
                            : 'bg-blue-600 text-white rounded-br-none shadow-sm'
                    "
                >
                    <p class="whitespace-pre-wrap">{{ message.text }}</p>
                    <span
                        class="text-[10px] mt-1 block text-right"
                        :class="
                            message.direction === 'incoming'
                                ? 'text-gray-400'
                                : 'text-blue-200'
                        "
                    >
                        {{
                            new Date(message.sent_at).toLocaleTimeString([], {
                                hour: "2-digit",
                                minute: "2-digit",
                            })
                        }}
                    </span>
                </div>
            </div>
        </div>

        <div class="p-3 border-t bg-white rounded-b-lg">
            <form @submit.prevent="sendMessage" class="flex gap-2">
                <input
                    type="text"
                    v-model="newMessageText"
                    placeholder="Напишите сообщение..."
                    class="flex-1 border-gray-300 rounded-full px-4 py-2 text-sm focus:ring-blue-500 focus:border-blue-500"
                    :disabled="!chat"
                />
                <button
                    type="submit"
                    :disabled="!newMessageText.trim() || !chat"
                    class="bg-blue-600 text-white rounded-full px-5 py-2 text-sm font-medium hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
                >
                    Отправить
                </button>
            </form>
        </div>
    </div>
</template>
