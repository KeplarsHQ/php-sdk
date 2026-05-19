<?php

namespace Keplars\Email\Resources;

use Keplars\Email\Client;

class Automations
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function list(?int $page = null, ?int $limit = null): array
    {
        $params = [];
        if ($page !== null) {
            $params['page'] = $page;
        }
        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        $query = Client::buildQueryString($params);
        return $this->client->request('GET', "/api/v1/public/automations/get-all{$query}")['data'];
    }

    public function get(string $id): array
    {
        return $this->client->request('GET', "/api/v1/public/automations/get-automation/{$id}")['data'];
    }

    public function enroll(string $id, string $email): array
    {
        return $this->client->request('POST', "/api/v1/public/automations/add-automation/{$id}/enroll", ['email' => $email])['data'];
    }

    public function unenroll(string $id, string $email): array
    {
        return $this->client->request('DELETE', "/api/v1/public/automations/delete-automation/{$id}/subscribers", ['email' => $email])['data'];
    }
}
