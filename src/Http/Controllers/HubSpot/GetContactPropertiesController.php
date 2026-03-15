<?php

namespace Lwekuiper\StatamicConnect\Http\Controllers\HubSpot;

use Lwekuiper\StatamicConnect\Facades\HubSpot;
use Statamic\Http\Controllers\Controller;
use Statamic\Support\Arr;

class GetContactPropertiesController extends Controller
{
    public function __invoke(): array
    {
        return collect(Arr::get(HubSpot::getContactProperties(), 'results', []))
            ->map(fn ($mergeField) => ['id' => $mergeField['name'], 'label' => $mergeField['label']])
            ->values()
            ->all();
    }
}
