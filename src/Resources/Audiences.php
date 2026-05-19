<?php

namespace Keplars\Email\Resources;

use Keplars\Email\Client;

class Audiences
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function create(string $name, ?string $description = null): array
    {
        $body = ['name' => $name];
        if ($description !== null) {
            $body['description'] = $description;
        }
        return $this->client->request('POST', '/api/v1/public/audiences/add-audience', $body)['data'];
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
        return $this->client->request('GET', "/api/v1/public/audiences/get-audiences{$query}")['data'];
    }

    public function get(string $id): array
    {
        return $this->client->request('GET', '/api/v1/public/audiences/get-audience?id=' . urlencode($id))['data'];
    }

    public function delete(string $id): array
    {
        return $this->client->request('DELETE', '/api/v1/public/audiences/delete-audience?id=' . urlencode($id))['data'];
    }
}
