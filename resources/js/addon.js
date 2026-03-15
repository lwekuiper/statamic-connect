import ConnectFormFields from './components/fieldtypes/ConnectFormFields.vue'
import ConnectRemoteList from './components/fieldtypes/ConnectRemoteList.vue'
import ConnectRemoteTag from './components/fieldtypes/ConnectRemoteTag.vue'
import ConnectMergeFields from './components/fieldtypes/ConnectMergeFields.vue'
import ConnectSites from './components/fieldtypes/ConnectSites.vue'

Statamic.booting(() => {
    Statamic.$components.register('connect_form_fields-fieldtype', ConnectFormFields)
    Statamic.$components.register('connect_remote_list-fieldtype', ConnectRemoteList)
    Statamic.$components.register('connect_remote_tag-fieldtype', ConnectRemoteTag)
    Statamic.$components.register('connect_merge_fields-fieldtype', ConnectMergeFields)
    Statamic.$components.register('connect_sites-fieldtype', ConnectSites)
})
