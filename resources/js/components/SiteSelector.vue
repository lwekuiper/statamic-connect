<template>
    <div v-if="sites.length > 1" class="site-selector">
        <select v-model="currentSite" @change="switchSite" class="form-select">
            <option v-for="site in sites" :key="site.handle" :value="site.handle">
                {{ site.name }}
            </option>
        </select>
    </div>
</template>

<script>
export default {
    props: {
        selected: {
            type: String,
            default: null,
        },
    },

    data() {
        return {
            sites: Statamic.$config.get('sites') || [],
            currentSite: this.selected,
        }
    },

    methods: {
        switchSite() {
            const url = new URL(window.location)
            url.searchParams.set('site', this.currentSite)
            window.location = url.toString()
        },
    },
}
</script>
