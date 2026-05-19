<?php

namespace Keplars\Email\Resources;

use Keplars\Email\Client;

class Contacts
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function add(array $params): array
    {
        return $this->client->request('POST', '/api/v1/public/contacts/add-contact', $params)['data'];
    }

    public function get(string $email): array
    {
        return $this->client->request('GET', '/api/v1/public/contacts/get-contact?email=' . urlencode($email))['data'];
    }

    public function list(?string $audienceId = null, ?int $page = null, ?int $limit = null): array
    {
        $params = [];
        if ($audienceId !== null) {
            $params['audience_id'] = $audienceId;
        }
        if ($page !== null) {
            $params['page'] = $page;
        }
        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        $query = Client::buildQueryString($params);
        return $this->client->request('GET', "/api/v1/public/contacts/get-contacts{$query}")['data'];
    }

    public function update(string $email, array $params): array
    {
        return $this->client->request('PATCH', '/api/v1/public/contacts/update-contact?email=' . urlencode($email), $params)['data'];
    }

    public function delete(string $email): array
    {
        return $this->client->request('DELETE', '/api/v1/public/contacts/delete-contact?email=' . urlencode($email))['data'];
    }
}
