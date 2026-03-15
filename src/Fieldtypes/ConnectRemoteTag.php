<?php

namespace Lwekuiper\StatamicConnect\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ConnectRemoteTag extends Fieldtype
{
    protected $selectable = false;

    protected static $handle = 'connect_remote_tag';

    protected function configFieldItems(): array
    {
        return [
            'integration' => [
                'type' => 'hidden',
            ],
        ];
    }
}
