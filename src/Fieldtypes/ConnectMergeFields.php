<?php

namespace Lwekuiper\StatamicConnect\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ConnectMergeFields extends Fieldtype
{
    protected $selectable = false;

    protected static $handle = 'connect_merge_fields';

    protected function configFieldItems(): array
    {
        return [
            'integration' => [
                'type' => 'hidden',
            ],
        ];
    }
}
