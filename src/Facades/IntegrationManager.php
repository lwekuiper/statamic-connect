<?php

namespace Lwekuiper\StatamicConnect\Facades;

use Illuminate\Support\Facades\Facade;

class IntegrationManager extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Lwekuiper\StatamicConnect\IntegrationManager::class;
    }
}
