<?php

namespace Revolut\Plugin\Infrastructure\Api\Auth;

use Revolut\Plugin\Infrastructure\Config\Api\Config;

interface AuthStrategyInterface
{
    public function getApiConfig(): Config;
    public function authenticateRequest(array $requestOptions): array;
    public function handleUnauthorizedResponse(array $previousResponse, callable $retryCallback): array;
    public function getBaseUrl(): string;
}
