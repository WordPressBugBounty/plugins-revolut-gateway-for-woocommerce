<?php

namespace Revolut\Plugin\Services\AuthConnect;

use Revolut\Plugin\Domain\Model\Token;

interface AuthConnectServiceInterface
{
    public function disconnect(string $mode): void;
    public function exchangeAuthorizationCode(string $mode, string $code, string $verifier): Token;
}
