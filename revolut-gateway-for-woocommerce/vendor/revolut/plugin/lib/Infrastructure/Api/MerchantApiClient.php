<?php

namespace Revolut\Plugin\Infrastructure\Api;

use Revolut\Plugin\Infrastructure\Api\Auth\AuthStrategyInterface;
use Revolut\Plugin\Services\Http\HttpClientInterface;
use Revolut\Plugin\Services\Log\RLog;

class MerchantApiClient implements MerchantApiClientInterface
{
    private $auth;
    private $http;
    private $legacyApi;
    private $headers;

    public const REVOLUT_API_VERSION = '2024-09-01';
    public const CONTENT_TYPE = 'application/json';

    public function __construct(AuthStrategyInterface $auth, HttpClientInterface $http, bool $legacyApi = false)
    {
        $this->auth = $auth;
        $this->http = $http;
        $this->legacyApi = $legacyApi;
        $this->headers = [
            'Content-Type' => self::CONTENT_TYPE,
            'Revolut-Api-Version' => self::REVOLUT_API_VERSION,
        ];
    }

    public function get(string $path, array $query = []): array
    {
        $url = $this->buildUrl($path);
        $options = $this->auth->authenticateRequest([
            'headers' => $this->headers,
            'query' => $query,
        ]);
        return $this->handleRequest('GET', $url, $options);
    }

    public function post(string $path, array $data = []): array
    {
        $url = $this->buildUrl($path);
        $options = $this->auth->authenticateRequest([
            'headers' => $this->headers,
            'body' => !empty($data) ? json_encode($data) : null,
        ]);
        return $this->handleRequest('POST', $url, $options);
    }

    public function put(string $path, array $data = []): array
    {
        $url = $this->buildUrl($path);
        $options = $this->auth->authenticateRequest([
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);
        return $this->handleRequest('PUT', $url, $options);
    }

    public function patch(string $path, array $data = []): array
    {
        $url = $this->buildUrl($path);
        $options = $this->auth->authenticateRequest([
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);
        return $this->handleRequest('PATCH', $url, $options);
    }

    public function delete(string $path, array $data = []): array
    {
        $url = $this->buildUrl($path);
        $options = $this->auth->authenticateRequest([
            'headers' => $this->headers,
            'body' => json_encode($data),
        ]);
        return $this->handleRequest('DELETE', $url, $options);
    }

    private function handleRequest(string $method, string $url, array $options): array
    {
        $response = $this->http->request($method, $url, $options);

        if ($this->http->getStatusCode($response) === 401) {
            $response = $this->auth->handleUnauthorizedResponse($response, function () use ($method, $url, $options) {
                $newOptions = $this->auth->authenticateRequest($options);
                return $this->http->request($method, $url, $newOptions);
            });
        }

        return $this->getBody($response, $method, $url);
    }

    private function getBody($response, $method, $url): array
    {
        $response_code = $this->http->getStatusCode($response);
        $response_body = $this->http->getBody($response);

        if ($response_code >= 400 && $response_code < 510 && 'GET' !== $method) {
            RLog::error("Failed request to URL $method $response_code $url - " . json_encode($response_body));
            throw new \Exception("api call failed: $method $url $response_code");
        }

        return $response_body;
    }

    private function buildUrl(string $path): string
    {
        return rtrim($this->auth->getBaseUrl(), '/') . ( $this->legacyApi ? '/1.0/' : '/' ) . ltrim($path, '/');
    }
}
