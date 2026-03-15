<template>
    <div class="connect-merge-fields">
        <div v-for="(mapping, index) in mappings" :key="index" class="flex items-center gap-2 mb-2">
            <v-select
                v-model="mapping.form_field"
                :options="formFields"
                :reduce="f => f.handle"
                label="display"
                placeholder="Form field..."
                class="flex-1"
                @input="update"
            />
            <span class="text-gray-500">&rarr;</span>
            <v-select
                v-model="mapping.remote_field"
                :options="remoteFields"
                :reduce="f => f.id"
                label="name"
                placeholder="Remote field..."
                class="flex-1"
                @input="update"
            />
            <button @click="removeMapping(index)" class="btn-close text-gray-600 hover:text-gray-800">
                &times;
            </button>
        </div>
        <button @click="addMapping" class="btn btn-sm">Add Field Mapping</button>
    </div>
</template>

<script>
export default {
    mixins: [Fieldtype],

    data() {
        return {
            mappings: [],
            formFields: [],
            remoteFields: [],
        }
    },

    computed: {
        integration() {
            return this.config.integration
        },
    },

    mounted() {
        this.mappings = Array.isArray(this.value) ? [...this.value] : []
        this.loadFormFields()
        this.loadRemoteFields()
    },

    methods: {
        loadFormFields() {
            const form = this.meta.form || this.$route?.params?.form
            if (!form) return

            this.$axios.get(cp_url(`connect/api/form-fields/${form}`)).then(response => {
                this.formFields = response.data
            })
        },

        loadRemoteFields() {
            if (!this.integration) return

            this.$axios.get(cp_url(`connect/api/${this.integration}/remote-fields`)).then(response => {
                this.remoteFields = response.data
            })
        },

        addMapping() {
            this.mappings.push({ form_field: null, remote_field: null })
            this.update()
        },

        removeMapping(index) {
            this.mappings.splice(index, 1)
            this.update()
        },

        update() {
            this.$emit('input', this.mappings)
        },
    },
}
</script>
