<?php

namespace Revolut\Plugin\Infrastructure\Config\Api;

use Revolut\Plugin\Services\Config\Api\ConfigInterface;

class Config implements ConfigInterface
{
    protected $mode;
    protected $clientId;
    protected $baseUrl;
    protected $connectServerUrl;
    protected $secretKey;
    protected $publicKey;

    public function getMode(): string
    {
        return $this->mode;
    }
    public function getClientId(): string
    {
        return $this->clientId;
    }

    public function getOAuthEndpoint(): string
    {
        return $this->getBaseUrl() . '/api/oauth';
    }

    public function getConnectServerUrl(): string
    {
        return $this->connectServerUrl;
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

    public function setSecretKey($secretKey): void
    {
        $this->secretKey = $secretKey;
    }

    public function getSecretKey(): string
    {
        return $this->secretKey;
    }

    public function setPublicKey($publicKey): void
    {
        $this->publicKey = $publicKey;
    }

    public function getPublicKey(): string
    {
        return $this->publicKey;
    }

    public function isLive(): bool
    {
        return $this->getMode() === Environment::PROD;
    }

    public function isDev(): bool
    {
        return $this->getMode() === Environment::DEV;
    }

    public function isSandbox(): bool
    {
        return $this->getMode() === Environment::SANDBOX;
    }
}
