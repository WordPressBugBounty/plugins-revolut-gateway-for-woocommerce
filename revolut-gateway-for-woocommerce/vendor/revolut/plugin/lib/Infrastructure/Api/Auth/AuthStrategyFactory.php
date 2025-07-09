<?php

namespace Revolut\Plugin\Infrastructure\Api\Auth;

use Revolut\Plugin\Services\Config\Api\ConfigProviderInterface;
use Revolut\Plugin\Infrastructure\Config\Api\Config;
use Revolut\Plugin\Services\Repositories\TokenRepositoryInterface;
use Revolut\Plugin\Services\Lock\LockInterface;
use Revolut\Plugin\Services\AuthConnect\TokenRefreshServiceInterface;

class AuthStrategyFactory
{
    private $tokenRefreshLockService;
    private $apiConfigProvider;
    private $repository;
    private $tokenRefreshService;

    public function __construct(
        ConfigProviderInterface $apiConfigProvider,
        LockInterface $tokenRefreshLockService,
        TokenRepositoryInterface $repository,
        TokenRefreshServiceInterface $tokenRefreshService
    ) {
        $this->apiConfigProvider = $apiConfigProvider;
        $this->tokenRefreshLockService = $tokenRefreshLockService;
        $this->repository = $repository;
        $this->tokenRefreshService = $tokenRefreshService;
    }

    public function getConfig(): Config
    {
        return $this->apiConfigProvider->getConfig();
    }

    public function createPrivateAuthStrategy(): AuthStrategyInterface
    {
        $secretKeyAuthStrategy = new SecretKeyAuthStrategy(
            $this->apiConfigProvider
        );

        if (!empty($this->getConfig()->getSecretKey())) {
            return $secretKeyAuthStrategy;
        }

        $token = $this->repository->getTokens();

        if (
            $token
            && $token->accessToken
            && $token->refreshToken
            && !$this->apiConfigProvider->getConfig()->isSandbox()
        ) {
            return new AccessTokenAuthStrategy(
                $this->tokenRefreshLockService,
                $this->apiConfigProvider,
                $this->repository,
                $this->tokenRefreshService
            );
        }

        return $secretKeyAuthStrategy;
    }

    public function createPublicAuthStrategy(): AuthStrategyInterface
    {
        return new PublicKeyAuthStrategy(
            $this->apiConfigProvider
        );
    }
}
