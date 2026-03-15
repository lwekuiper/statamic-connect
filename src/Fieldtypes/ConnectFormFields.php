<?php

namespace Lwekuiper\StatamicConnect\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ConnectFormFields extends Fieldtype
{
    protected $selectable = false;

    protected static $handle = 'connect_form_fields';

    protected function configFieldItems(): array
    {
        return [];
    }
}
