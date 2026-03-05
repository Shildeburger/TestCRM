<script setup>
import { useForm, router } from "@inertiajs/vue3";
import ContactForm from "./Partials/ContactForm.vue";
import AuthLayout from "../../Layouts/AuthLayout.vue";

const props = defineProps({
    contact: Object,
});

const form = useForm({
    name: props.contact.name ?? "",
    email: props.contact.email ?? "",
    phone: props.contact.phone ?? "",
    company: props.contact.company ?? "",
    job_title: props.contact.job_title ?? "",
    country: props.contact.country ?? "",
    notes: props.contact.notes ?? "",
    is_favorite: !!props.contact.is_favorite, //!!props.contact.is_favorite - это двойное отрицание,
    //  которое используется для преобразования значения props.contact.is_favorite в булев тип (true или false).
    //  Почему нужно использовать двойное отрицание? - Это может быть полезно, если props.contact.is_favorite
    //  может быть неопределенным или null, и мы хотим гарантировать, что form.is_favorite будет иметь булевое значение
    //  (true или false).
});

function submit() {
    form.put(route("contacts.update", props.contact.id)); // form.put - это метод объекта формы,
    //  который используется для отправки данных на сервер с помощью HTTP PUT запроса.
    //  Он принимает два аргумента: URL, на который будет отправлен запрос, и объект с дополнительными параметрами,
    //  такими как обработчики событий.
    //  PUT-запрос - это тип HTTP-запроса, который обычно используется для обновления существующих ресурсов на сервере.
    //  В данном случае, мы используем form.put для отправки данных формы на сервер, чтобы обновить существующий контакт
    //  с идентификатором props.contact.id. Когда мы вызываем form.put(route("contacts.update", props.contact.id)),
    //  мы отправляем данные формы на сервер, и если запрос успешен, мы можем выполнить дополнительные действия,
    //  указанные в объекте параметров. В данном случае, мы не указываем никаких дополнительных параметров,
    //  поэтому после успешного обновления контакта
    //  мы просто остаемся на той же странице, и любые изменения в данных контакта будут отображаться благодаря реактивности Vue.
    //  props.contact.id - это идентификатор контакта, который мы хотим обновить. Он передается в URL маршрута "contacts.update",
    //  чтобы сервер мог определить, какой контакт нужно обновить с помощью данных, отправленных в форме.
}

function cancel() {
    router.visit(route("contacts.index"));
}
</script>

<template>
    <AuthLayout>
        <div class="p-6">
            <h1 class="text-xl font-bold mb-4">Edit</h1>

            <ContactForm
                :form="form"
                :errors="form.errors"
                @submit="submit"
                @cancel="cancel"
            />
        </div>
    </AuthLayout>
</template>
