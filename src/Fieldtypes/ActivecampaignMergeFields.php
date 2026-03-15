<?php

namespace Lwekuiper\StatamicConnect\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ActivecampaignMergeFields extends Fieldtype
{
    protected $component = 'activecampaign_merge_fields';

    public static function handle()
    {
        return 'activecampaign_merge_fields';
    }
}
