<?php

namespace Lwekuiper\StatamicConnect\Fieldtypes;

use Statamic\Fields\Fieldtype;

class HubspotContactProperties extends Fieldtype
{
    protected $component = 'hubspot_contact_properties';

    public static function handle()
    {
        return 'hubspot_contact_properties';
    }
}
