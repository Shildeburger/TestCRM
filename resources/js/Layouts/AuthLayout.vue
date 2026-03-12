<script setup>
import { computed } from "vue";
import { usePage, Link, router } from "@inertiajs/vue3";

const page = usePage();

const flashSuccess = computed(() => page.props.flash?.success || null);
const flashError = computed(() => page.props.flash?.error || null);
</script>

<template>
    <div class="min-h-screen bg-gray-100">
        <!-- Шапка с данными пользователя -->
        <header class="bg-white border-b mb-4">
            <div
                class="max-w-6xl mx-auto px-4 py-3 flex justify-between items-center"
            >
                <Link :href="route('dashboard')" class="font-semibold">
                    My CRM
                </Link>
                <div v-if="page.props.auth?.user" class="text-sm text-gray-700">
                    {{ page.props.auth.user.name }}
                </div>
            </div>
        </header>

        <!-- Flash -->
        <div class="max-w-6xl mx-auto px-4">
            <div
                v-if="flashSuccess"
                class="mb-3 px-3 py-2 bg-green-100 text-green-800 text-sm rounded"
            >
                {{ flashSuccess }}
            </div>

            <div
                v-if="flashError"
                class="mb-3 px-3 py-2 bg-red-100 text-red-800 text-sm rounded"
            >
                {{ flashError }}
            </div>
        </div>

        <!-- Контент страниц -->
        <main class="max-w-6xl mx-auto px-4 pb-10">
            <slot />
        </main>
    </div>
</template>
