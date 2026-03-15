<template>
    <div v-if="sites.length > 1">
        <v-select
            v-model="selectedSite"
            :options="sites"
            :reduce="site => site.handle"
            label="name"
            placeholder="Select site..."
            @input="onSiteChange"
        />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    data() {
        return {
            sites: Statamic.$config.get('sites') || [],
            selectedSite: this.value,
        }
    },

    methods: {
        onSiteChange(site) {
            this.$emit('input', site)
        },
    },
}
</script>
