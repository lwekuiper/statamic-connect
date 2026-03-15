import ConnectListing from './components/listing/ConnectListing.vue';
import PublishForm from './components/publish/PublishForm.vue';
import ACMergeFieldsField from './components/ActiveCampaign/ActiveCampaignMergeFieldsFieldtype.vue';
import HSContactPropertiesField from './components/HubSpot/HubSpotContactPropertiesFieldtype.vue';
import FormFieldsField from './components/fieldtypes/StatamicFormFieldsFieldtype.vue';
import SitesField from './components/fieldtypes/ConnectSitesFieldtype.vue';
import Index from './pages/Index.vue';
import Empty from './pages/Empty.vue';
import Edit from './pages/Edit.vue';

Statamic.booting(() => {
    Statamic.$inertia.register('connect::Index', Index);
    Statamic.$inertia.register('connect::Empty', Empty);
    Statamic.$inertia.register('connect::Edit', Edit);

    Statamic.$components.register('connect-listing', ConnectListing);
    Statamic.$components.register('connect-publish-form', PublishForm);
    Statamic.$components.register('activecampaign_merge_fields-fieldtype', ACMergeFieldsField);
    Statamic.$components.register('hubspot_contact_properties-fieldtype', HSContactPropertiesField);
    Statamic.$components.register('statamic_form_fields-fieldtype', FormFieldsField);
    Statamic.$components.register('connect_sites-fieldtype', SitesField);
});
