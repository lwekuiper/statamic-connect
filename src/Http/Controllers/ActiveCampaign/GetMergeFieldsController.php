<?php

namespace Lwekuiper\StatamicConnect\Http\Controllers\ActiveCampaign;

use Illuminate\Support\Arr;
use Lwekuiper\StatamicConnect\Facades\ActiveCampaign;
use Statamic\Http\Controllers\Controller;

class GetMergeFieldsController extends Controller
{
    public function __invoke(): array
    {
        $standardFields = [
            ['id' => 'email', 'label' => 'Email'],
            ['id' => 'firstName', 'label' => 'First Name'],
            ['id' => 'lastName', 'label' => 'Last Name'],
            ['id' => 'phone', 'label' => 'Phone'],
        ];

        $response = ActiveCampaign::getCustomFields();

        $customFields = collect(Arr::get($response, 'fields', []))
            ->map(fn ($customField) => [
                'id' => $customField['id'],
                'label' => $customField['title'],
            ])
            ->values()
            ->all();

        return array_merge($standardFields, $customFields);
    }
}
