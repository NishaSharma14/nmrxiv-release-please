<template>
    <div>
        <nav
            aria-label="Pagination"
            class="max-w-7xl mx-auto px-4 mt-6 flex justify-between text-sm font-medium text-gray-700 sm:px-6 lg:px-8"
        >
            <div class="min-w-0 flex-1">
                <Link
                    :href="links[0].url"
                    class="inline-flex items-center px-4 h-10 border border-gray-300 rounded-md bg-white hover:bg-gray-100 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-offset-1 focus:ring-offset-indigo-600 focus:ring-indigo-600 focus:ring-opacity-25"
                >
                    Previous
                </Link>
            </div>
            <div class="hidden space-x-2 sm:flex">
                <Link
                    v-for="link in links.slice(1, -1)"
                    :key="link.url"
                    :href="link.url"
                    :class="[
                        link.active
                            ? 'border-indigo-600 ring-1 ring-indigo-600 '
                            : '',
                        'inline-flex items-center px-4 h-10 border border-gray-300 rounded-md bg-white hover:bg-gray-100 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-offset-1 focus:ring-offset-indigo-600 focus:ring-indigo-600 focus:ring-opacity-25',
                    ]"
                >
                    {{ link.label }}
                </Link>
            </div>
            <div
                v-if="links[links.length - 1]"
                class="min-w-0 flex-1 flex justify-end"
            >
                <Link
                    :href="links[links.length - 1].url"
                    class="inline-flex items-center px-4 h-10 border border-gray-300 rounded-md bg-white hover:bg-gray-100 focus:outline-none focus:border-indigo-600 focus:ring-2 focus:ring-offset-1 focus:ring-offset-indigo-600 focus:ring-indigo-600 focus:ring-opacity-25"
                >
                    Next
                </Link>
            </div>
        </nav>
    </div>
</template>

<script>
import { Link } from "@inertiajs/inertia-vue3";
export default {
    props: {
        links: Array,
    },
    components: {
        Link,
    },
};
</script>
