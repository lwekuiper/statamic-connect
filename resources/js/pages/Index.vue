<script setup>
import { Head } from '@statamic/cms/inertia';

defineProps({
    createFormUrl: String,
    configureUrl: String,
    integration: String,
    formConfigs: Array,
    localizations: Array,
    site: String,
});
</script>

<template>
    <Head :title="__('Connect')" />
    <connect-listing
        :create-form-url="createFormUrl"
        :configure-url="configureUrl"
        :integration="integration"
        :initial-form-configs="formConfigs"
        :initial-localizations="localizations"
        :initial-site="site"
    />
</template>
