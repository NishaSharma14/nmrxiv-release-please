<template>
    <div>
        <div class="md:flex md:flex-col">
            <div class="md:h-screen md:flex md:flex-col">
                <div class="md:flex md:flex-shrink-0">
                    <div
                        class="bg-indigo-900 md:flex-shrink-0 md:w-56 px-6 py-4 flex items-center justify-between md:justify-center"
                    >
                        <inertia-link class="mt-1" href="/">
                            <logo class="fill-white" width="120" height="28" />
                        </inertia-link>
                        <dropdown class="md:hidden" placement="bottom-end">
                            <svg
                                class="fill-white w-6 h-6"
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 20 20"
                            >
                                <path
                                    d="M0 3h20v2H0V3zm0 6h20v2H0V9zm0 6h20v2H0v-2z"
                                />
                            </svg>
                            <template #dropdown>
                                <div
                                    class="mt-2 px-8 py-4 shadow-lg bg-indigo-800 rounded"
                                >
                                    <main-menu />
                                </div>
                            </template>
                        </dropdown>
                    </div>
                    <div
                        class="bg-white border-b w-full p-4 md:py-0 md:px-12 text-sm md:text-md flex justify-between items-center"
                    >
                        <div class="mt-1 mr-4">{{ user.account.name }}</div>
                        <dropdown class="mt-1" placement="bottom-end">
                            <div
                                class="flex items-center cursor-pointer select-none group"
                            >
                                <div
                                    class="text-gray-700 group-hover:text-indigo-600 focus:text-indigo-600 mr-1 whitespace-no-wrap"
                                >
                                    <span>{{ user.first_name }}</span>
                                    <span class="hidden md:inline">{{
                                        user.last_name
                                    }}</span>
                                </div>
                                <icon
                                    class="w-5 h-5 group-hover:fill-indigo-600 fill-gray-700 focus:fill-indigo-600"
                                    name="cheveron-down"
                                />
                            </div>
                            <template #dropdown>
                                <div
                                    class="mt-2 py-2 shadow-xl bg-white rounded text-sm"
                                >
                                    <inertia-link
                                        class="block px-6 py-2 hover:bg-indigo-500 hover:text-white"
                                        :href="
                                            route('console.users.edit', user.id)
                                        "
                                        >My Profile</inertia-link
                                    >
                                    <inertia-link
                                        class="block px-6 py-2 hover:bg-indigo-500 hover:text-white"
                                        :href="route('users')"
                                        >Manage Users</inertia-link
                                    >
                                    <inertia-link
                                        class="block px-6 py-2 hover:bg-indigo-500 hover:text-white"
                                        :href="route('logout')"
                                        method="post"
                                        >Logout</inertia-link
                                    >
                                </div>
                            </template>
                        </dropdown>
                    </div>
                </div>
                <div class="md:flex md:flex-grow md:overflow-hidden">
                    <main-menu
                        class="hidden md:block bg-indigo-800 flex-shrink-0 w-56 p-12 overflow-y-auto"
                    />
                    <div
                        class="md:flex-1 px-4 py-8 md:p-12 md:overflow-y-auto"
                        scroll-region
                    >
                        <flash-messages />
                        <slot />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import Dropdown from "@/Shared/Dropdown.vue";
import FlashMessages from "@/Shared/FlashMessages.vue";
import Icon from "@/Shared/Icon.vue";
import Logo from "@/Shared/Logo.vue";
import MainMenu from "@/Shared/MainMenu.vue";
import { computed } from "vue";
import { usePage } from "@inertiajs/inertia-vue3";

export default {
    components: {
        Dropdown,
        FlashMessages,
        Icon,
        Logo,
        MainMenu,
    },
    setup() {
        const user = computed(() => usePage().props.value.auth.user);
        return { user };
    },
};
</script>
