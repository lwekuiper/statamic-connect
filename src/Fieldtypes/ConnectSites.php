<?php

namespace Lwekuiper\StatamicConnect\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ConnectSites extends Fieldtype
{
    protected $selectable = false;

    protected static $handle = 'connect_sites';

    protected function configFieldItems(): array
    {
        return [];
    }
}
