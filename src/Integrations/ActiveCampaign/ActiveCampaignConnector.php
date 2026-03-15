<?php

namespace Lwekuiper\StatamicConnect\Integrations\ActiveCampaign;

use Lwekuiper\StatamicConnect\Integrations\BaseConnector;

class ActiveCampaignConnector extends BaseConnector
{
    protected function baseUrl(): string
    {
        return rtrim(config('statamic.connect.integrations.activecampaign.api_url'), '/').'/api/3/';
    }

    protected function headers(): array
    {
        return [
            'Api-Token' => config('statamic.connect.integrations.activecampaign.api_key'),
        ];
    }

    public function syncContact(string $email, array $data = []): ?array
    {
        return $this->post('contact/sync', [
            'contact' => array_merge(['email' => $email], $data),
        ]);
    }

    public function updateListStatus(string $contactId, string $listId, int $status = 1): ?array
    {
        return $this->post('contactLists', [
            'contactList' => [
                'list' => $listId,
                'contact' => $contactId,
                'status' => $status,
            ],
        ]);
    }

    public function addTagToContact(string $contactId, string $tagId): ?array
    {
        return $this->post('contactTags', [
            'contactTag' => [
                'contact' => $contactId,
                'tag' => $tagId,
            ],
        ]);
    }

    public function getLists(): ?array
    {
        $response = $this->get('lists', ['limit' => 100]);

        return collect($response['lists'] ?? [])->map(fn ($list) => [
            'id' => $list['id'],
            'name' => $list['name'],
        ])->all();
    }

    public function getTags(): ?array
    {
        $response = $this->get('tags', ['limit' => 100]);

        return collect($response['tags'] ?? [])->map(fn ($tag) => [
            'id' => $tag['id'],
            'name' => $tag['tag'],
        ])->all();
    }

    public function getCustomFields(): ?array
    {
        $response = $this->get('fields', ['limit' => 100]);

        return collect($response['fields'] ?? [])->map(fn ($field) => [
            'id' => $field['id'],
            'name' => $field['title'],
            'type' => $field['type'],
        ])->all();
    }
}
