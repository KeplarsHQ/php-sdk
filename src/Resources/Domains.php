<?php

namespace Keplars\Email\Resources;

use Keplars\Email\Client;

class Domains
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function add(string $domain): array
    {
        return $this->client->request('POST', '/api/v1/public/domains/add-domain', ['domain' => $domain])['data'];
    }

    public function list(): array
    {
        return $this->client->request('GET', '/api/v1/public/domains/get-domains')['data'];
    }

    public function getStatus(string $domainId): array
    {
        return $this->client->request('GET', "/api/v1/public/domains/domain-status/{$domainId}")['data'];
    }

    public function verify(string $domainId): array
    {
        return $this->client->request('POST', "/api/v1/public/domains/verify-domain/{$domainId}")['data'];
    }

    public function delete(string $domainId): array
    {
        return $this->client->request('DELETE', "/api/v1/public/domains/delete-domain/{$domainId}")['data'];
    }

    public function createApiKey(array $params): array
    {
        return $this->client->request('POST', '/api/v1/public/domains/api-keys/create', $params)['data'];
    }
}
