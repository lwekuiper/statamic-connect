<template>
    <div>
        <v-select
            v-model="value"
            :options="lists"
            :reduce="list => list.id"
            label="name"
            :multiple="true"
            placeholder="Select lists..."
            :loading="loading"
        />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    data() {
        return {
            lists: [],
            loading: false,
        }
    },

    computed: {
        integration() {
            return this.config.integration
        },
    },

    mounted() {
        this.loadLists()
    },

    methods: {
        loadLists() {
            if (!this.integration) return

            this.loading = true
            this.$axios.get(cp_url(`connect/api/${this.integration}/remote-lists`)).then(response => {
                this.lists = response.data
            }).finally(() => {
                this.loading = false
            })
        },
    },
}
</script>
