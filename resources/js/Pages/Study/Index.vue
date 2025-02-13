<template>
    <div>
        <div class="flex items-baseline justify-between">
            <div>
                <h2 class="text-lg">Studies</h2>
                <div v-if="!loading" class="mt-2 text-sm text-gray-700">
                    <div>
                        <input
                            placeholder="search"
                            @blur="update()"
                            type="text"
                            v-model="query"
                            class="rounded-md w-full border-gray-200"
                        />
                    </div>
                </div>
            </div>
            <!-- <div class="flex-shrink-0 ml-4">
                <button
                    v-if="editable"
                    type="button"
                    class="inline-flex items-center px-2.5 py-1.5 border border-gray-300 shadow-sm text-sm font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-teal-500"
                    @click="openStudyCreateDialog()"
                >
                    New Study
                </button>
            </div> -->
        </div>
        <div v-if="!loading && studies.data">
            <div v-if="studies.data.length <= 0 && editable">
                <div class="mt-4 px-12 py-8 mx-auto max-w-4xl">
                    <div class="px-6 py-4 bg-white shadow-md rounded-lg">
                        <div class="flex items-center">
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                viewBox="0 0 24 24"
                                class="h-6 w-6"
                            >
                                <path
                                    d="M3 6l9 4v12l-9-4V6zm14-3v2c0 1.1-2.24 2-5 2s-5-.9-5-2V3c0 1.1 2.24 2 5 2s5-.9 5-2z"
                                    class="fill-current text-gray-400"
                                ></path>
                                <polygon
                                    points="21 6 12 10 12 22 21 18"
                                    class="fill-current text-gray-600"
                                ></polygon>
                            </svg>
                            <div
                                class="ml-3 font-semibold text-sm text-gray-600 uppercase tracking-wider"
                            >
                                Create Your First Study
                            </div>
                        </div>
                        <div class="mt-3 max-w-2xl text-sm text-gray-700">
                            nmrXiv is organized around studies. A project can
                            contain as many studies as you wish and each study
                            receives its very own URL. To learn more, check out
                            our documentation.
                        </div>
                        <button
                            type="button"
                            class="relative block w-full border-2 border-gray-300 border-dashed rounded-lg p-12 text-center hover:border-gray-400 focus:outline-none focus:ring-2 mt-6 focus:ring-offset-2 focus:ring-teal-500"
                            @click="openStudyCreateDialog()"
                        >
                            <svg
                                class="mx-auto h-12 w-12 text-gray-400"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                aria-hidden="true"
                            >
                                <path
                                    vector-effect="non-scaling-stroke"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="2"
                                    d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z"
                                />
                            </svg>
                            <span
                                class="mt-2 block text-sm font-medium text-gray-900"
                            >
                                Create a new study
                            </span>
                        </button>
                    </div>
                </div>
            </div>
            <div v-else>
                <div
                    class="mt-8 mx-auto max-w-md grid gap-8 sm:max-w-lg lg:grid-cols-3 lg:max-w-7xl"
                >
                    <div v-for="study in studies.data" :key="study.uuid">
                        <study-card :study="study" />
                    </div>
                </div>
                <div class="block w-100 mt-10">
                    <nav
                        class="border-t border-gray-200 px-4 flex items-center justify-between sm:px-0"
                    >
                        <div class="-mt-px w-0 flex-1 flex">&nbsp;</div>
                        <div class="hidden md:-mt-px md:flex">
                            <div
                                v-for="link in studies.links"
                                :key="link.url"
                                @click="update(link)"
                                v-html="link.label"
                                :class="[
                                    link.active
                                        ? 'text-teal-600 border-teal-500'
                                        : '',
                                    'cursor-pointer border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300 border-t-2 pt-4 px-4 inline-flex items-center text-sm font-medium',
                                ]"
                            ></div>
                        </div>
                        <div class="-mt-px w-0 flex-1 flex justify-end">
                            &nbsp;
                        </div>
                    </nav>
                </div>
            </div>
        </div>
        <div class="text-gray-400" v-else>
            <svg
                class="animate-spin inline -ml-1 mr-2 h-5 w-5 text-dark"
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
            >
                <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                ></circle>
                <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                ></path>
            </svg>
            Loading...
        </div>
        <!-- <study-create :project="project"></study-create> -->
    </div>
</template>

<script>
import { Link } from "@inertiajs/inertia-vue3";
import StudyCreate from "@/Pages/Study/Partials/Create.vue";
import StudyCard from "@/Shared/StudyCard.vue";
import JetButton from "@/Jetstream/Button.vue";
import {
    ArrowNarrowLeftIcon,
    ArrowNarrowRightIcon,
} from "@heroicons/vue/solid";

export default {
    components: {
        Link,
        StudyCreate,
        StudyCard,
        JetButton,
        ArrowNarrowLeftIcon,
        ArrowNarrowRightIcon,
    },
    props: {
        project: {
            default: null,
            type: Object,
        },
        role: {
            default: null,
            type: String,
        },
    },
    data() {
        return {
            loading: false,
            studies: [],
            query: "",
        };
    },
    mounted() {
        if (this.studies.length == 0) {
            if (this.project) {
                this.loading = true;
                this.fetchStudies(
                    route("dashboard.project.studies", this.project.id)
                );
            }
        }
    },
    methods: {
        fetchStudies(url) {
            axios.get(url).then((response) => {
                this.loading = false;
                this.studies = response.data.studies;
            });
        },
        update(link) {
            if (!link && this.query != "") {
                link = {};
                link["url"] =
                    route("dashboard.project.studies", this.project.id) +
                    "?page=1";
            }
            if (link.url) {
                this.loading = true;
                this.fetchStudies(link.url + "&query=" + this.query);
            }
        },
        openStudyCreateDialog() {
            this.emitter.emit("openStudyCreateDialog", {});
        },
    },
};
</script>
