<template>
    <div
        v-if="projects && projects.length > 0"
        class="mt-4 mx-auto max-w-md grid gap-8 sm:max-w-lg lg:grid-cols-4 lg:max-w-7xl"
    >
        <span v-for="project in projects" :key="project.id">
            <project-card :mode="'mini'" :project="project"></project-card>
        </span>
    </div>
</template>

<script>
import ProjectCard from "@/Shared/ProjectCard.vue";

export default {
    components: {
        ProjectCard,
    },
    props: [],
    setup() {},
    data() {
        return {
            projects: [],
        };
    },
    mounted() {
        axios.get("/api/v1/projects").then((response) => {
            this.projects = response.data.data;
        });
    },
    methods: {},
};
</script>
