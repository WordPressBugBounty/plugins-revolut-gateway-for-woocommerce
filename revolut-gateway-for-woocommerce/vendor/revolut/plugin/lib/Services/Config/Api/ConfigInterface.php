<?php

namespace Revolut\Plugin\Services\Config\Api;

interface ConfigInterface
{
    public function getMode(): string;

    public function getClientId(): string;

    public function getOAuthEndpoint(): string;

    public function getConnectServerUrl(): string;

    public function getBaseUrl(): string;

    public function setSecretKey($secretKey): void;

    public function getSecretKey(): string;

    public function setPublicKey($publicKey): void;

    public function getPublicKey(): string;

    public function isLive(): bool;

    public function isDev(): bool;

    public function isSandbox(): bool;
}
