<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    value: { required: true },
    meta: { type: Object, default: () => ({}) },
    namePrefix: { type: String, default: '' },
});

const emit = defineEmits(['update:value']);

const fields = ref([]);

const form = computed(() => props.meta.form ?? '');

// Detect integration from the current URL path
const integration = computed(() => {
    const path = window.location.pathname;
    if (path.includes('/connect/activecampaign')) return 'activecampaign';
    if (path.includes('/connect/hubspot')) return 'hubspot';
    return 'activecampaign';
});

onMounted(() => {
    refreshFields();
});

function refreshFields() {
    axios
        .get(cp_url(`/connect/${integration.value}/form-fields/${form.value}`))
        .then(response => {
            fields.value = response.data;
        })
        .catch(() => { fields.value = []; });
}
</script>

<template>
    <div class="statamic-form-fields-fieldtype-wrapper">
        <ui-combobox
            class="w-full"
            :model-value="value"
            @update:model-value="emit('update:value', $event)"
            :options="fields"
            optionValue="id"
            optionLabel="label"
            :label="__('Choose...')"
            :clearable="true"
            :searchable="true"
        />
    </div>
</template>
