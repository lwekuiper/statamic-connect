<?php

namespace Lwekuiper\StatamicConnect\Exceptions;

use Exception;

class FormConfigNotFoundException extends Exception
{
    public function __construct(string $id)
    {
        parent::__construct("Form config [{$id}] not found.");
    }
}
