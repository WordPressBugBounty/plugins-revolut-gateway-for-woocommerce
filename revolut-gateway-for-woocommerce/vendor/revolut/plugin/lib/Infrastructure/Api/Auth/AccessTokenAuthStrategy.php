<?php

namespace Revolut\Plugin\Infrastructure\Api\Auth;

use Exception;
use Revolut\Plugin\Services\Repositories\TokenRepositoryInterface;
use Revolut\Plugin\Domain\Model\Token;
use Revolut\Plugin\Services\Log\RLog;
use Revolut\Plugin\Services\AuthConnect\Exceptions\TokenRefreshInProgressException;
use Revolut\Plugin\Infrastructure\Config\Api\Config;
use Revolut\Plugin\Services\Config\Api\ConfigProviderInterface;
use Revolut\Plugin\Services\AuthConnect\TokenRefreshServiceInterface;
use Revolut\Plugin\Services\Lock\LockInterface;

class AccessTokenAuthStrategy implements AuthStrategyInterface
{
    private $accessToken;
    private $refreshToken;

    private $lock;
    private $apiConfigProvider;
    private $repository;
    private $tokenRefreshService;

    private const RETRY = 5;

    public function __construct(
        LockInterface $lock,
        ConfigProviderInterface $apiConfigProvider,
        TokenRepositoryInterface $repository,
        TokenRefreshServiceInterface $tokenRefreshService
    ) {
        $this->lock = $lock;
        $this->apiConfigProvider = $apiConfigProvider;
        $this->repository = $repository;
        $this->tokenRefreshService = $tokenRefreshService;
        $this->setToken(null);
    }

    public function getApiConfig(): Config
    {
        return $this->apiConfigProvider->getConfig();
    }

    private function setToken(?Token $token): void
    {
        if (!$token) {
            $token = $this->repository->getTokens();
        }

        if (!$token) {
            return;
        }

        $this->accessToken = $token->accessToken;
        $this->refreshToken = $token->refreshToken;
    }

    public function authenticateRequest(array $requestOptions): array
    {
        $requestOptions['headers']['Authorization'] = 'Bearer ' . $this->accessToken;
        return $requestOptions;
    }

    public function handleUnauthorizedResponse(array $previousResponse, callable $retryCallback): array
    {
        try {
            RLog::info('try token refresh with - ' . $this->accessToken);
            $token = $this->repository->getTokens();

            if ($this->accessToken !== $token->accessToken) {
                RLog::info('token already refreshed');
                $this->accessToken = $token->accessToken;
                $this->refreshToken = $token->refreshToken;
                return $retryCallback();
            }

            $token = $this->tokenRefreshService->tryRefreshTokenWithLock();
            $this->setToken($token);
        } catch (TokenRefreshInProgressException $e) {
            RLog::info('token refresh in progress for - ' . $this->accessToken);
            //@TODO Remove wait logic after FE side retries are implemented
            $waitUpToSec = 0;
            $token = $this->repository->getTokens();

            while ($this->lock->isLocked() && $waitUpToSec < self::RETRY) {
                $token = $this->repository->getTokens();
                $waitUpToSec++;
                sleep(1);
            }

            RLog::info('awaited ' . ( $waitUpToSec ) . 's long');

            $token = $this->repository->getTokens();

            if ($this->lock->isLocked() || $this->accessToken === $token->accessToken) {
                throw new Exception('Token refreshing process took more than expected');
            }

            RLog::info('old access token ' . $this->accessToken);
            RLog::info('updated access token ' . $token->accessToken);

            $this->setToken($token);
        }

        return $retryCallback();
    }

    public function getBaseUrl(): string
    {
        return $this->getApiConfig()->getBaseUrl() . '/api';
    }
}
