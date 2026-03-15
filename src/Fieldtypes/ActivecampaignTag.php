<?php

namespace Lwekuiper\StatamicConnect\Fieldtypes;

use Lwekuiper\StatamicConnect\Facades\ActiveCampaign;
use Statamic\Fieldtypes\Relationship;
use Statamic\Support\Arr;

class ActivecampaignTag extends Relationship
{
    public static function handle()
    {
        return 'activecampaign_tag';
    }

    public function getIndexItems($request)
    {
        $response = ActiveCampaign::getTags();

        $tags = Arr::get($response, 'tags', []);

        return collect($tags)->map(fn ($tag) => [
            'id' => $tag['id'],
            'title' => $tag['tag'],
        ])->toArray();
    }

    protected function toItemArray($id)
    {
        if (! $id) {
            return [];
        }

        $response = ActiveCampaign::getTags();
        $tags = Arr::get($response, 'tags', []);
        $tag = collect($tags)->firstWhere('id', $id);

        if (! $tag) {
            return [];
        }

        return [
            'id' => $tag['id'],
            'title' => $tag['tag'],
        ];
    }
}
