<script setup>
import { Head, useArchitecturalBackground } from '@statamic/cms/inertia';
import { EmptyStateMenu, EmptyStateItem } from '@statamic/cms/ui';
import { computed } from 'vue';

const props = defineProps({
    createUrl: String,
    integration: String,
});

const integrationName = computed(() => {
    const names = {
        activecampaign: 'ActiveCampaign',
        hubspot: 'HubSpot',
        klaviyo: 'Klaviyo',
        brevo: 'Brevo',
        salesforce: 'Salesforce',
    };
    return names[props.integration] || props.integration;
});

useArchitecturalBackground();
</script>

<template>
    <Head :title="integrationName" />

    <header class="py-8 mt-8 text-center starting-style-transition" v-cloak>
        <h1 class="text-[25px] font-medium antialiased flex justify-center items-center gap-2 sm:gap-3">
            <span v-text="integrationName" />
        </h1>
    </header>

    <EmptyStateMenu :heading="__('Connect a Statamic form and map your fields.')">
        <EmptyStateItem
            :href="createUrl"
            icon="forms"
            :heading="__('Create Form')"
            :description="__('Get started by creating a Statamic form first.')"
        />
    </EmptyStateMenu>
</template>
