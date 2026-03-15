<?php

namespace Lwekuiper\StatamicConnect\Facades;

use Illuminate\Support\Facades\Facade;
use Lwekuiper\StatamicConnect\Connectors\HubSpotConnector;

class HubSpot extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return HubSpotConnector::class;
    }
}
