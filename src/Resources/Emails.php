<?php

namespace Keplars\Email\Resources;

use Keplars\Email\Client;

class Emails
{
    private Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function sendInstant(array $params): array
    {
        return $this->client->request('POST', '/api/v1/public/send-email/instant', $params)['data'];
    }

    public function sendHigh(array $params): array
    {
        return $this->client->request('POST', '/api/v1/public/send-email/high', $params)['data'];
    }

    public function sendAsync(array $params): array
    {
        return $this->client->request('POST', '/api/v1/public/send-email/async', $params)['data'];
    }

    public function sendBulk(array $params): array
    {
        return $this->client->request('POST', '/api/v1/public/send-email/bulk', $params)['data'];
    }

    public function send(array $params): array
    {
        return $this->sendAsync($params);
    }

    public function schedule(array $params): array
    {
        if (!isset($params['priority'])) {
            $params['priority'] = 'async';
        }
        return $this->client->request('POST', '/api/v1/public/send-email/schedule', $params)['data'];
    }
}
