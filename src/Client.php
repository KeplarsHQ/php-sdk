<?php

namespace Keplars\Email;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;
use Keplars\Email\Resources\Emails;
use Keplars\Email\Resources\Contacts;
use Keplars\Email\Resources\Audiences;
use Keplars\Email\Resources\Automations;
use Keplars\Email\Resources\Domains;
use Keplars\Email\Exceptions\KeplarsException;
use Keplars\Email\Exceptions\ValidationException;
use Keplars\Email\Exceptions\AuthenticationException;
use Keplars\Email\Exceptions\AuthorizationException;
use Keplars\Email\Exceptions\DomainNotVerifiedException;
use Keplars\Email\Exceptions\RateLimitException;
use Keplars\Email\Exceptions\InternalException;
use Keplars\Email\Exceptions\NetworkException;

class Client
{
    private const DEFAULT_BASE_URL = 'https://api.keplars.com';
    private const DEFAULT_TIMEOUT = 30;
    private const DEFAULT_MAX_RETRIES = 3;
    private const DEFAULT_RETRY_DELAY = 1;
    private const VERSION = '1.10.8';

    private string $apiKey;
    private string $baseUrl;
    private int $timeout;
    private int $maxRetries;
    private float $retryDelay;
    private GuzzleClient $httpClient;

    public Emails $emails;
    public Contacts $contacts;
    public Audiences $audiences;
    public Automations $automations;
    public Domains $domains;

    public function __construct(
        string $apiKey = '',
        ?string $baseUrl = null,
        int $timeout = self::DEFAULT_TIMEOUT,
        int $maxRetries = self::DEFAULT_MAX_RETRIES,
        float $retryDelay = self::DEFAULT_RETRY_DELAY
    ) {
        if (empty($apiKey)) {
            $apiKey = $_ENV['KEPLARS_API_KEY'] ?? getenv('KEPLARS_API_KEY') ?: '';
            if (empty($apiKey)) {
                throw new \InvalidArgumentException('API key is required. Set KEPLARS_API_KEY or pass apiKey parameter');
            }
        }

        if (!$this->validateApiKey($apiKey)) {
            throw new \InvalidArgumentException('Invalid API key format. Expected: kms_<id>.live_<secret> or kms_<id>.adm_<secret>');
        }

        $this->apiKey = $apiKey;

        $resolvedBaseUrl = $baseUrl
            ?? ($_ENV['KEPLARS_BASE_URL'] ?? getenv('KEPLARS_BASE_URL'))
            ?: self::DEFAULT_BASE_URL;

        $this->baseUrl = rtrim($resolvedBaseUrl, '/');
        $this->timeout = $timeout;
        $this->maxRetries = $maxRetries;
        $this->retryDelay = $retryDelay;

        $this->httpClient = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'User-Agent' => 'keplars-email-php/' . self::VERSION,
            ],
        ]);

        $this->emails = new Emails($this);
        $this->contacts = new Contacts($this);
        $this->audiences = new Audiences($this);
        $this->automations = new Automations($this);
        $this->domains = new Domains($this);
    }

    public function request(string $method, string $path, ?array $body = null, int $retryCount = 0): array
    {
        try {
            $options = [];
            if ($body !== null) {
                $options['json'] = $body;
            }

            $response = $this->httpClient->request($method, $path, $options);

            $rateLimitInfo = $this->extractRateLimitInfo($response);

            $data = json_decode($response->getBody()->getContents(), true);

            return [
                'data' => $data,
                'rate_limit_info' => $rateLimitInfo,
            ];
        } catch (RequestException $e) {
            if ($e->hasResponse()) {
                return $this->handleErrorResponse($e->getResponse(), $retryCount, $method, $path, $body);
            }

            if ($this->isRetryable($e) && $retryCount < $this->maxRetries) {
                $delay = $this->calculateBackoff($retryCount);
                usleep((int)($delay * 1000000));
                return $this->request($method, $path, $body, $retryCount + 1);
            }

            throw new NetworkException('Request failed: ' . $e->getMessage(), 0, $e);
        }
    }

    private function extractRateLimitInfo($response): ?array
    {
        $limit = $response->getHeaderLine('X-RateLimit-Limit');
        $remaining = $response->getHeaderLine('X-RateLimit-Remaining');
        $reset = $response->getHeaderLine('X-RateLimit-Reset');

        if (empty($limit) || empty($remaining) || empty($reset)) {
            return null;
        }

        return [
            'limit' => (int)$limit,
            'remaining' => (int)$remaining,
            'reset' => (int)$reset,
        ];
    }

    private function handleErrorResponse($response, int $retryCount, string $method, string $path, ?array $body): never
    {
        $rawBody = $response->getBody()->getContents();
        $errorData = json_decode($rawBody, true);

        $statusCode = $response->getStatusCode();
        $message = $errorData['message'] ?? "HTTP {$statusCode}";

        switch ($statusCode) {
            case 400:
                throw new ValidationException($message, null, null);
            case 401:
                throw new AuthenticationException($message, null);
            case 403:
                throw new AuthorizationException($message, null);
            case 429:
                throw new RateLimitException($message, 60, null);
            case 500:
            case 502:
            case 503:
                if ($retryCount < $this->maxRetries) {
                    $delay = $this->calculateBackoff($retryCount);
                    usleep((int)($delay * 1000000));
                }
                throw new InternalException($message, null);
            default:
                throw new KeplarsException($message, 'UNKNOWN_ERROR');
        }
    }

    private function calculateBackoff(int $retryCount): float
    {
        $exponentialDelay = $this->retryDelay * pow(2, $retryCount);
        $jitter = (mt_rand() / mt_getrandmax()) * 0.3 * $exponentialDelay;
        return min($exponentialDelay + $jitter, 30.0);
    }

    private function isRetryable(\Exception $e): bool
    {
        return $e instanceof \GuzzleHttp\Exception\ConnectException ||
               $e instanceof \GuzzleHttp\Exception\ServerException;
    }

    public static function buildQueryString(array $params): string
    {
        $filtered = array_filter($params, fn($v) => $v !== null && $v !== '');

        if (empty($filtered)) {
            return '';
        }

        return '?' . http_build_query($filtered);
    }

    private function validateApiKey(string $apiKey): bool
    {
        return preg_match('/^kms_[a-f0-9]+\.(live|adm)_[a-f0-9]+$/', $apiKey) === 1;
    }
}
