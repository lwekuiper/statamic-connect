<?php

namespace Lwekuiper\StatamicConnect\Fieldtypes;

use Lwekuiper\StatamicConnect\Facades\ActiveCampaign;
use Statamic\Fieldtypes\Relationship;
use Statamic\Support\Arr;

class ActivecampaignList extends Relationship
{
    public static function handle()
    {
        return 'activecampaign_list';
    }

    public function getIndexItems($request)
    {
        $response = ActiveCampaign::getLists();

        $lists = Arr::get($response, 'lists', []);

        return collect($lists)->map(fn ($list) => [
            'id' => $list['id'],
            'title' => $list['name'],
        ])->toArray();
    }

    protected function toItemArray($id)
    {
        if (! $id) {
            return [];
        }

        $response = ActiveCampaign::getLists();
        $lists = Arr::get($response, 'lists', []);
        $list = collect($lists)->firstWhere('id', $id);

        if (! $list) {
            return [];
        }

        return [
            'id' => $list['id'],
            'title' => $list['name'],
        ];
    }
}
