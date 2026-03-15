<template>
    <div v-if="tags.length > 0">
        <v-select
            v-model="value"
            :options="tags"
            :reduce="tag => tag.id"
            label="name"
            :multiple="true"
            placeholder="Select tags..."
            :loading="loading"
        />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    data() {
        return {
            tags: [],
            loading: false,
        }
    },

    computed: {
        integration() {
            return this.config.integration
        },
    },

    mounted() {
        this.loadTags()
    },

    methods: {
        loadTags() {
            if (!this.integration) return

            this.loading = true
            this.$axios.get(cp_url(`connect/api/${this.integration}/remote-tags`)).then(response => {
                this.tags = response.data
            }).finally(() => {
                this.loading = false
            })
        },
    },
}
</script>
