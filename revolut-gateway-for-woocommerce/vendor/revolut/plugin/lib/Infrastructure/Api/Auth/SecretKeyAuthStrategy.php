<?php

namespace Revolut\Plugin\Infrastructure\Api\Auth;

use Revolut\Plugin\Services\Config\Api\ConfigProviderInterface;
use Revolut\Plugin\Infrastructure\Config\Api\Config;

class SecretKeyAuthStrategy implements AuthStrategyInterface
{
    private $apiConfigProvider;

    public function __construct(ConfigProviderInterface $apiConfigProvider)
    {
        $this->apiConfigProvider = $apiConfigProvider;
    }

    public function getApiConfig(): Config
    {
        return $this->apiConfigProvider->getConfig();
    }

    public function authenticateRequest(array $requestOptions): array
    {
        $requestOptions['headers']['Authorization'] = 'Bearer ' . $this->getApiConfig()->getSecretKey();
        return $requestOptions;
    }

    public function getBaseUrl(): string
    {
        return $this->getApiConfig()->getBaseUrl() . '/api';
    }

    public function handleUnauthorizedResponse(array $previousResponse, callable $retryCallback): array
    {
        return $previousResponse;
    }
}
