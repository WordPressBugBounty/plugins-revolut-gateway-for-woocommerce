<?php

namespace Revolut\Plugin\Infrastructure\Config\Api;

use Revolut\Plugin\Infrastructure\Config\Api\Environment;
use Revolut\Plugin\Infrastructure\Config\Api\ApiConfig;
use RuntimeException;

class SandboxConfig extends Config
{
    protected $mode = Environment::SANDBOX;
    protected $baseUrl = 'https://sandbox-merchant.revolut.com';

    public function getClientId(): string
    {
        throw new RuntimeException("oauth not supported in sandbox");
    }

    public function getOAuthEndpoint(): string
    {
        throw new RuntimeException("oauth not supported in sandbox");
    }

    public function getConnectServerUrl(): string
    {
        throw new RuntimeException("oauth not supported in sandbox");
    }
}
