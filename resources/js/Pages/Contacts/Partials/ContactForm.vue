<script setup>
const props = defineProps({
    //defineProps - это функция, которая позволяет компоненту принимать входные данные
    //  от родительского компонента. В данном случае, мы определяем два свойства: form и errors.
    form: Object, //form - это объект, который содержит данные формы, такие как имя, email, телефон и т.д.
    //  Этот объект будет использоваться для связывания данных формы с полями ввода в шаблоне.
    errors: Object, //errors - это объект, который содержит ошибки валидации для каждого поля формы.
    //  Если форма была отправлена и сервер вернул ошибки, они будут доступны в этом объекте.
    //  В шаблоне мы будем отображать эти ошибки рядом с соответствующими полями ввода.
});

const emit = defineEmits(["submit", "cancel"]); //defineEmits - это функция, которая позволяет компоненту
// отправлять события родительскому компоненту.
//  В данном случае, мы определяем два события: submit и cancel.
//  Эти события будут отправляться, когда пользователь нажимает на кнопки "Save" и "Cancel" соответственно.

//А какой компонент в данном случае является родительским? Это может быть любой компонент, который использует\
//  ContactForm.vue и передает ему данные через props. Например, это может быть компонент
//  Create.vue или Edit.vue, которые импортируют ContactForm.vue и используют его в своем шаблоне.
//  Когда пользователь нажимает на кнопку "Save" в ContactForm.vue, он вызывает emit("submit"),
//  что отправляет событие "submit" родительскому компоненту (например, Create.vue),
//  который может обработать это событие и выполнить соответствующие действия (например, отправить данные формы на сервер).
//  Аналогично, когда пользователь нажимает на кнопку "Cancel", он вызывает emit("cancel"),
//  что отправляет событие "cancel" родительскому компоненту, который может обработать это событие
//  (например, перенаправить пользователя обратно на список контактов).
</script>

<template>
    <form @submit.prevent="emit('submit')">
        <!-- form @submit.prvent="emit('submit')" - Это форма HTML,
         которая обрабатывает событие отправки. Когда пользователь нажимает на кнопку "Save",
          форма пытается отправиться, но благодаря @submit.prevent, мы предотвращаем стандартное поведение браузера
           (перезагрузку страницы) и вместо этого вызываем emit('submit'),
            что отправляет событие "submit" родительскому компоненту.
             Это позволяет нам обрабатывать отправку формы в родительском компоненте, например,
              отправлять данные на сервер или выполнять другие действия.
              form - это HTML элемент, который позвляет создать форму для заполнения
              @submit.prevent - это директива Vue, которая позволяет обработать событие отправки формы и предотвратить его
               стандартное поведение (перезагрузку страницы).
              emit('submit') - это вызов функции emit, которая отправляет событие "submit" родительскому компоненту.
               Родительский компонент может слушать это событие и выполнять соответствующие действия, например,
             отправлять данные формы на сервер.
        -->
        <div class="mb-3">
            <label class="block text-sm mb-1">Name</label>
            <input
                v-model="form.name"
                type="text"
                class="border px-2 py-1 rounded w-full"
            />
            <div v-if="errors.name" class="text-red-600 text-xs">
                {{ errors.name }}
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm mb-1">Phone</label>
            <input
                v-model="form.phone"
                type="text"
                class="border px-2 py-1 rounded w-full"
            />
            <div v-if="errors.phone" class="text-red-600 text-xs">
                {{ errors.phone }}
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm mb-1">Email</label>
            <input
                v-model="form.email"
                type="text"
                class="border px-2 py-1 rounded w-full"
            />
            <div v-if="errors.email" class="text-red-600 text-xs">
                {{ errors.email }}
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm mb-1">Company</label>
            <input
                v-model="form.company"
                type="text"
                class="border px-2 py-1 rounded w-full"
            />
            <div v-if="errors.company" class="text-red-600" text-xs>
                {{ errors.company }}
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm mb-1">Job title</label>
            <input
                v-model="form.job_title"
                type="text"
                class="border px-2 py-1 rounded w-full"
            />
            <div v-if="errors.job_title" class="text-red-600 text-xs">
                {{ errors.job_title }}
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm mb-1">Country</label>
            <input
                v-model="form.country"
                type="text"
                class="border px-2 py-1 rounded w-full"
            />
            <div v-if="errors.country" class="text-red-600 text-xs">
                {{ errors.country }}
            </div>
        </div>

        <div class="mb-3">
            <label class="block text-sm mb-1">Notes</label>
            <textarea
                v-model="form.notes"
                rows="3"
                class="border px-2 py-1 rounded w-full"
            />
            <div v-if="errors.notes" class="text-red-600 text-xs">
                {{ errors.notes }}
            </div>
        </div>

        <div class="mb-4">
            <label class="inline-flex items-center gap-2">
                <input v-model="form.is_favourite" type="checkbox" />
                <span>Favorite</span>
            </label>
        </div>

        <div class="flex gap-2">
            <button
                type="submit"
                class="px-3 py-1 bg-blue-600 text-white rounded"
                :disabled="form.processing"
            >
                Save
            </button>

            <button
                type="button"
                class="px-3 py-1 border rounded"
                @click="emit('cancel')"
            >
                Cancel
            </button>
        </div>
    </form>
</template>
