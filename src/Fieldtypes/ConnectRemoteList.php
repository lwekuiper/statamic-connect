<?php

namespace Lwekuiper\StatamicConnect\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ConnectRemoteList extends Fieldtype
{
    protected $selectable = false;

    protected static $handle = 'connect_remote_list';

    protected function configFieldItems(): array
    {
        return [
            'integration' => [
                'type' => 'hidden',
            ],
        ];
    }
}
