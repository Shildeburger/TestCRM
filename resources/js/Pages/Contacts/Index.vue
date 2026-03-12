<script setup>
//Index.vue - это файл компонента Vue, который отвечает за отображение страницы со списком контактов.
//  Он использует Composition API для управления состоянием и логикой компонента.
//  В этом файле мы определяем реактивные переменные для фильтров и сортировки, а также функцию для перезагрузки данных
//  при изменении этих фильтров. Мы также используем шаблон для отображения списка контактов в виде таблицы,
//  с возможностью редактирования и удаления каждого контакта, а также пагинации для навигации между страницами контактов.

//setup - это новый способ писать компоненты во Vue 3, который позволяет использовать Composition API
//  без необходимости создавать отдельные функции setup() и возвращать объект.
//  Все переменные и функции, объявленные в <script setup>, автоматически доступны в шаблоне компонента.
//  Это делает код более компактным и удобным для чтения.
import { ref, watch, onMounted, onUnmounted } from "vue"; //ref - это функция из Vue, которая позволяет создавать реактивные переменные.
//  Когда значение ref изменяется, Vue автоматически обновляет все места, где это значение используется в шаблоне.
//watch - это функция из Vue, которая позволяет отслеживать изменения в реактивных переменных
//  и выполнять определенные действия при их изменении.
import { router, Link } from "@inertiajs/vue3"; //router - это объект из Inertia.js, который позволяет управлять навигацией
//  в приложении.
//  Он предоставляет методы для перехода на другие страницы, отправки форм и т.д.
//Link - это компонент из Inertia.js, который используется для создания ссылок в приложении.
import AuthLayout from "../../Layouts/AuthLayout.vue"; //AuthLayout - это компонент, который используется для обертки страниц,
//  требующих аутентификации.

const props = defineProps({
    //defineProps - это функция из Vue, которая позволяет определять свойства,
    //  которые компонент ожидает получить от родительского компонента. props - это объект, который будет содержать
    //  все переданные свойства. От какого объекта передаются свойства? Обычно это родительский компонент,
    //  который использует этот компонент и передает ему данные через атрибуты. Например:
    // <ContactsIndex :contacts="contacts" :filters="filters" :countries="countries" />
    contacts: Object, //Пагинированный список. Пагинированный значит - это объект, который содержит массив данных
    //  (contacts.data) и информацию о пагинации (contacts.links). Пагинация - это процесс разделения большого количества
    // данных на страницы, чтобы улучшить производительность и удобство использования.
    filters: Object, //{ search, country, favorite, sort, direction }
    countries: Array, //список стран для select
});

const search = ref(props.filters.search || ""); //Реактивная переменная для хранения значения поля поиска.
//  Инициализируется значением из props.filters.search или пустой строкой, если оно не задано.
// search - поиск по имени, email, компании и т.д. в списке контактов.
// props - это объект, который содержит все свойства, переданные в компонент.
//  В данном случае, мы ожидаем, что в props будет объект filters, который содержит текущие значения фильтров,
//  и массив countries для выпадающего списка стран.
// filters - это объект, который содержит текущие значения фильтров, таких как search, country, favorite, sort и direction.
// || "" - это оператор логического ИЛИ, который используется для установки значения по умолчанию. То есть по умолчанию search
//  будет пустой строкой, если props.filters.search не задано или равно undefined.
const country = ref(props.filters.country || ""); //Реактивная переменная для хранения выбранной страны в фильтре.
const favorite = ref(props.filters.favorite || false); //Реактивная переменная для хранения состояния фильтра "только избранные".
//  Инициализируется значением из props.filters.favorite. По умолчанию, если props.filters.favorite не задано,
//  будет false (не только избранные).
const sort = ref(props.filters.sort || "name"); //Реактивная переменная для хранения выбранного поля сортировки.
//  Инициализируется значением из props.filters.sort или "name" по умолчанию.
//props.filters.sort - это значение сортировки, которое может быть передано в компонент через props.
//  Например, если родительский компонент передает <ContactsIndex :filters="{ sort: 'company' }" />,
// то props.filters.sort будет равно "company". Если же сортировка не передана, то по умолчанию будет "name".
const direction = ref(props.filters.direction || "asc"); //Реактивная переменная для хранения выбранного направления сортировки
//  (asc или desc). Инициализируется значением из props.filters.direction или "asc" по умолчанию. Asc - это сокращение от
//  "ascending" (по возрастанию), а desc - от "descending" (по убыванию).

function reload() {
    //Функция для перезагрузки данных с новыми параметрами фильтрации и сортировки. Вызывается
    //  при изменении любого из фильтров.
    router.get(
        //Метод get объекта router используется для отправки GET-запроса на сервер.
        //  В данном случае, он используется для запроса обновленных данных контактов с учетом новых фильтров и сортировки.
        // GET-запрос - это тип HTTP-запроса, который используется для получения данных с сервера. Когда мы вызываем router.get(),
        //  мы отправляем запрос на указанный URL, и сервер возвращает данные, которые мы можем использовать в нашем приложении.
        //  В данном случае, мы запрашиваем обновленный список контактов с учетом новых фильтров и сортировки.
        route("contacts.index"), //route - это функция, которая генерирует URL для заданного маршрута.
        //  В данном случае, мы генерируем URL для маршрута "contacts.index",
        //  который, вероятно, соответствует странице со списком контактов.
        //  Этот URL будет использоваться для отправки GET-запроса на сервер, чтобы получить обновленные данные контактов
        {
            search: search.value, //search.value - это текущее значение поля поиска,
            //  которое мы хотим отправить на сервер для фильтрации контактов.
            //value - это свойство объекта ref, которое содержит текущее значение реактивной переменной.
            //  Когда мы используем ref для создания реактивной переменной,
            //  мы получаем объект с единственным свойством value, которое содержит текущее значение этой переменной.
            //  Чтобы получить или установить значение, мы используем .value. Например, search - это ref, и чтобы получить
            //  его текущее значение, мы пишем search.value. Когда мы изменяем search.value, Vue автоматически отслеживает
            //  это изменение и обновляет все места в шаблоне, где используется search.value.
            country: country.value,
            favourite: favorite.value ? 1 : 0, //favorite.value - это текущее значение чекбокса "только избранные".
            //  Если он отмечен (true), мы отправляем 1, иначе 0. Это может быть полезно для серверной части,
            //  которая ожидает числовое значение для фильтрации избранных контактов.
            sort: sort.value, //sort.value - это текущее значение выбранного поля сортировки,
            //  которое мы отправляем на сервер для сортировки контактов.
            direction: direction.value,
        }, // Это объект с параметрами, который мы отправляем на сервер.
        //  Он содержит текущие значения всех фильтров и сортировки, которые сервер будет использовать для обработки запроса
        // и возвращения соответствующих данных контактов.
        {
            preserveState: true,
            replace: true,
        }, // Это объект с опциями для метода router.get(). preserveState: true означает, что при навигации на новую страницу
        //  сохраняется состояние текущей страницы, такое как scroll position, form data и т.д. replace: true означает,
        //  что при навигации на новую страницу не будет добавлена новая запись в историю браузера,
        //  а текущая запись будет заменена. Это полезно для предотвращения создания большого количества записей
    );
}

watch([search, country, favorite, sort, direction], () => {
    // watch - это функция из Vue,
    //  которая позволяет отслеживать изменения в реактивных переменных и выполнять определенные действия при их изменении.
    // В данном случае, мы используем watch для отслеживания изменений в фильтрах (search, country, favorite, sort, direction).
    //  Когда любое из этих значений изменяется, мы вызываем функцию reload(),
    //  которая отправляет запрос на сервер для получения обновленных данных контактов с учетом новых фильтров и сортировки.
    reload();
});

//Логика работы с WebSockets (Reverb)
onMounted(() => {
    //Подключаемся к приватному каналу 'contacts'
    window.Echo.private("contacts").listen(
        "TelegramContactCreated",
        (event) => {
            const newContact = {
                id: event.id,
                name: event.name,
                telegram_username: event.telegram_username,
                telegram_user_id: event.telegram_user_id,
                email: null,
                phone: null,
                company: null,
                country: null,
                is_favorite: false,
            };
            props.contacts.data.unshift(newContact);
        },
    );
});

onUnmounted(() => {
    window.Echo.leave("contacts");
});
</script>

<template>
    <AuthLayout>
        <div class="p-6">
            <div class="flex justify-between mb-4">
                <h1 class="text-xl font-bold">Contacts</h1>

                <Link
                    :href="route('contacts.create')"
                    class="px-3 py-1 bg-blue-600 text-white rounded"
                >
                    New contact
                </Link>
            </div>

            <div class="flex gap-2 mb-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by name, email..."
                    class="border px-2 py-1 rounded w-64"
                />

                <select v-model="country" class="border px-2 py-1 rounded">
                    <option value="">All countries</option>
                    <option v-for="c in countries" :key="c" :value="c">
                        {{ c }}
                    </option>
                </select>

                <label class="flex items-center gap-1">
                    <input v-model="favorite" type="checkbox" />
                    <span>Favourites only</span>
                </label>

                <select v-model="sort" class="border px-2 py-1 rounded">
                    <option value="name">Name</option>
                    <option value="company">Company</option>
                    <option value="country">Country</option>
                </select>

                <select v-model="direction" class="border px-2 py-1 rounded">
                    <option value="asc">Asc</option>
                    <option value="desc">Desc</option>
                </select>
            </div>

            <div v-if="contacts.data.length === 0" class="text-gray-500">
                No contats found.
            </div>

            <table v-else class="w-full text-sm border-collapse">
                <thead>
                    <tr class="border-b">
                        <th class="text-left py-2">Fav</th>
                        <th class="text-left py-2">Name</th>
                        <th class="text-left py-2">Email</th>
                        <th class="text-left py-2">Phone</th>
                        <th class="text-left py-2">Company</th>
                        <th class="text-left py-2">Country</th>
                        <th class="text-left py-2">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr
                        v-for="contact in contacts.data"
                        :key="contact.id"
                        class="border-b"
                    >
                        <td class="py-1">
                            <button
                                type="button"
                                @click="
                                    router.post(
                                        route('contacts.favorite', contact.id),
                                        {},
                                        { preserveScroll: true },
                                    )
                                "
                            >
                                {{ contact.is_favorite ? "⭐" : "☆" }}
                            </button>
                        </td>
                        <td class="py-1">
                            <Link :href="route('contacts.show', contact.id)">
                                {{ contact.name }}
                            </Link>
                        </td>
                        <td class="py-1">
                            {{ contact.email }}
                        </td>
                        <td class="py-1">
                            {{ contact.phone }}
                        </td>
                        <td class="py-1">
                            {{ contact.company }}
                        </td>
                        <td class="py-1">
                            {{ contact.country }}
                        </td>
                        <td class="py-1 text-right">
                            <Link
                                :href="route('contacts.edit', contact.id)"
                                class="text-blue-600 mr-2"
                            >
                                Edit
                            </Link>
                            <button
                                type="button"
                                class="text-red-600"
                                @click="
                                    router.delete(
                                        route('contacts.destroy', contact.id),
                                        { preserveScroll: true },
                                    )
                                "
                            >
                                Delete
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

            <div
                v-if="contacts.links && contacts.links.length > 1"
                class="flex gap-2 mt-4"
            >
                <button
                    v-for="link in contacts.links"
                    :key="link.label"
                    v-html="link.label"
                    :disabled="!link.url"
                    @click="
                        link.url &&
                        router.visit(link.url, {
                            preserveState: true,
                            replace: true,
                        })
                    "
                    class="px-2 py-1 border roubded"
                    :class="{
                        'bg-blue-600 text-white': link.active,
                        'text-gray-500': link.url,
                    }"
                />
            </div>
        </div>
    </AuthLayout>
</template>
