<?php

namespace Revolut\Plugin\Services\AuthConnect;

use Revolut\Plugin\Domain\Model\Token;

interface TokenRefreshServiceInterface
{
    public function tryRefreshTokenWithLock(): Token;
    public function refreshToken(): Token;
}
