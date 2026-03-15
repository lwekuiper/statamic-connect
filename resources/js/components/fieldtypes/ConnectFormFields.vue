<template>
    <div>
        <v-select
            v-model="value"
            :options="fields"
            :reduce="field => field.handle"
            label="display"
            :placeholder="meta.placeholder || 'Select a field...'"
            :clearable="true"
        />
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    data() {
        return {
            fields: [],
        }
    },

    mounted() {
        this.loadFields()
    },

    methods: {
        loadFields() {
            const form = this.meta.form || this.$route?.params?.form
            if (!form) return

            this.$axios.get(cp_url(`connect/api/form-fields/${form}`)).then(response => {
                this.fields = response.data
            })
        },
    },
}
</script>
