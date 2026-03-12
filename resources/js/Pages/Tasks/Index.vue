<script setup>
import { ref } from "vue";
import { useForm, router } from "@inertiajs/vue3";
import axios from "axios";
import FullCalendar from "@fullcalendar/vue3";
import dayGridPlugin from "@fullcalendar/daygrid";
import timeGridPlugin from "@fullcalendar/timegrid";
import interactionPlugin from "@fullcalendar/interaction";
import ruLocale from "@fullcalendar/core/locales/ru";
import AuthLayout from "@/Layouts/AuthLayout.vue";

const calendarRef = ref(null);
const isModalOpen = ref(false);
const isEditing = ref(false);

const form = useForm({
    id: null,
    title: "",
    description: "",
    start: "",
    end: "",
    color: "#3b82f6",
});

const calendarOptions = ref({
    plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
    initialView: "dayGridMonth",
    headerToolbar: {
        left: "prev,next today",
        center: "title",
        right: "dayGridMonth,timeGridWeek,timeGridDay",
    },
    locale: ruLocale,
    editable: true,
    selectable: true,

    events: function (info, successCallback, failureCallback) {
        axios
            .get("/tasks", {
                params: { start: info.startStr, end: info.endStr },
                headers: { Accept: "application/json" },
            })
            .then((response) => {
                successCallback(response.data);
            })
            .catch((error) => {
                console.error("Ошибка загрузки событий:", error);
                failureCallback(error);
            });
    },

    dateClick: (info) => {
        isEditing.value = false;
        form.reset();
        form.start = info.dateStr + (info.allDay ? "T12:00" : "");
        form.color = "#3b82f6";
        isModalOpen.value = true;
    },

    eventClick: (info) => {
        isEditing.value = true;
        form.id = info.event.id;
        form.title = info.event.title;
        form.description = info.event.extendedProps.description || "";
        form.start = formatDateTime(info.event.start);
        form.end = info.event.end ? formatDateTime(info.event.end) : "";
        form.color = info.event.backgroundColor || "#3b82f6";
        isModalOpen.value = true;
    },

    eventDrop: (info) => updateEventDates(info.event),
    eventResize: (info) => updateEventDates(info.event),
});

const formatDateTime = (dateObj) => {
    if (!dateObj) return "";
    const offset = dateObj.getTimezoneOffset() * 60000;
    return new Date(dateObj - offset).toISOString().slice(0, 16);
};

const submitForm = () => {
    if (isEditing.value) {
        form.put(`/tasks/${form.id}`, { onSuccess: handleSuccess });
    } else {
        form.post("/tasks", { onSuccess: handleSuccess });
    }
};

const deleteTask = () => {
    if (confirm("Вы уверены, что хотите удалить эту задачу?")) {
        router.delete(`/tasks/${form.id}`, { onSuccess: handleSuccess });
    }
};

const updateEventDates = (event) => {
    router.put(
        `/tasks/${event.id}`,
        {
            title: event.title,
            start: formatDateTime(event.start),
            end: event.end ? formatDateTime(event.end) : null,
            color: event.backgroundColor,
        },
        { preserveScroll: true },
    );
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.clearErrors();
};

const handleSuccess = () => {
    closeModal();
    if (calendarRef.value) {
        calendarRef.value.getApi().refetchEvents();
    }
};
</script>

<template>
    <AuthLayout>
        <div class="max-w-7xl mx-auto p-6 bg-gray-50 min-h-screen">
            <h1 class="text-3xl font-bold mb-6 text-gray-800">Таск-менеджер</h1>

            <div
                class="bg-white p-6 rounded-lg shadow [&_.fc-button-primary]:bg-blue-600 [&_.fc-button-primary]:border-blue-600 [&_.fc-button-primary]:capitalize [&_.fc-button-primary:hover]:bg-blue-700 [&_.fc-button-primary:hover]:border-blue-700 [&_.fc-button-primary.fc-button-active:not(:disabled)]:bg-blue-800 [&_.fc-button-primary.fc-button-active:not(:disabled)]:border-blue-800"
            >
                <FullCalendar ref="calendarRef" :options="calendarOptions" />
            </div>

            <div
                v-if="isModalOpen"
                class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
            >
                <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md">
                    <h2 class="text-xl font-bold mb-4">
                        {{
                            isEditing ? "Редактировать задачу" : "Новая задача"
                        }}
                    </h2>

                    <form @submit.prevent="submitForm">
                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Название</label
                            >
                            <input
                                v-model="form.title"
                                type="text"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500"
                                required
                            />
                            <div
                                v-if="form.errors.title"
                                class="text-red-500 text-xs mt-1"
                            >
                                {{ form.errors.title }}
                            </div>
                        </div>

                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Описание</label
                            >
                            <textarea
                                v-model="form.description"
                                rows="3"
                                class="mt-1 w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500"
                            ></textarea>
                        </div>

                        <div class="grid grid-cols-2 gap-4 mb-4">
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Начало</label
                                >
                                <input
                                    v-model="form.start"
                                    type="datetime-local"
                                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500"
                                    required
                                />
                                <div
                                    v-if="form.errors.start"
                                    class="text-red-500 text-xs mt-1"
                                >
                                    {{ form.errors.start }}
                                </div>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700"
                                    >Конец</label
                                >
                                <input
                                    v-model="form.end"
                                    type="datetime-local"
                                    class="mt-1 w-full border-gray-300 rounded-md shadow-sm p-2 border focus:ring-blue-500 focus:border-blue-500"
                                />
                            </div>
                        </div>

                        <div class="mb-4">
                            <label
                                class="block text-sm font-medium text-gray-700"
                                >Цвет метки</label
                            >
                            <input
                                v-model="form.color"
                                type="color"
                                class="mt-1 h-10 w-full cursor-pointer rounded-md border border-gray-300"
                            />
                        </div>

                        <div class="flex justify-between items-center mt-6">
                            <button
                                v-if="isEditing"
                                type="button"
                                @click="deleteTask"
                                class="text-red-600 hover:text-red-800 text-sm font-medium"
                            >
                                Удалить
                            </button>
                            <div v-else></div>

                            <div class="flex gap-2">
                                <button
                                    type="button"
                                    @click="closeModal"
                                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 font-medium"
                                >
                                    Отмена
                                </button>
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 font-medium disabled:opacity-50"
                                >
                                    Сохранить
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </AuthLayout>
</template>
