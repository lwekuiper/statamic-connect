<?php

namespace Lwekuiper\StatamicConnect\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicConnect\Stache\FormConfigRepository;

class FormConfig extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return FormConfigRepository::class;
    }
}
