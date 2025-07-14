<?php

namespace Revolut\Plugin\Services\AuthConnect;

use Exception;
use Revolut\Plugin\Domain\Model\Token;
use Revolut\Plugin\Services\Config\Api\ConfigProviderInterface;
use Revolut\Plugin\Services\Repositories\TokenRepositoryInterface;
use Revolut\Plugin\Services\Http\HttpClientInterface;
use Revolut\Plugin\Services\Lock\LockInterface;
use Revolut\Plugin\Services\Log\RLog;
use Revolut\Plugin\Services\AuthConnect\Exceptions\TokenRefreshInProgressException;
use Revolut\Plugin\Services\AuthConnect\TokenRefreshServiceInterface;
use Revolut\Plugin\Services\Config\Api\ConfigInterface;

class AuthConnect implements TokenRefreshServiceInterface, AuthConnectServiceInterface
{
    private $repository;
    private $httpClient;
    private $apiConfigProvider;
    private $lock;

    public function __construct(
        TokenRepositoryInterface $repo,
        HttpClientInterface $httpClient,
        ConfigProviderInterface $apiConfigProvider,
        LockInterface $lock
    ) {
        $this->lock = $lock;
        $this->repository = $repo;
        $this->httpClient = $httpClient;
        $this->apiConfigProvider = $apiConfigProvider;
    }

    public function getApiConfig(?string $mode = null): ConfigInterface
    {
        return $this->apiConfigProvider->getConfig($mode);
    }

    public function exchangeAuthorizationCode(string $mode, string $code, string $verifier): Token
    {
        $configProvider = $this->getApiConfig($mode);

        $params = array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'code_verifier' => $verifier,
            'client_id'     =>  $configProvider->getClientId(),
        );

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $options = array(
            'body' => http_build_query($params),
            'headers' => $headers,
        );

        $response = $this->httpClient->request(
            'POST',
            $configProvider->getOAuthEndpoint() . '/token',
            $options
        );

        $body = $this->httpClient->getBody($response);

        if (empty($body['access_token']) || empty($body['refresh_token'])) {
            throw new \Exception('Invalid Token response  - ' . json_encode($body));
        }

        $token = new Token($body['access_token'], $body['refresh_token']);
        $this->repository->saveTokens($token);
        return $token;
    }

    public function disconnect(string $mode): void
    {
        $configProvider = $this->getApiConfig($mode);

        $params = array(
            'client_id' => $configProvider->getClientId(),
        );

        $token = $this->repository->getTokens();

        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token->accessToken,
        );

        $options = array(
            'body' => json_encode($params),
            'headers' => $headers,
        );

        $this->httpClient->request(
            'POST',
            $configProvider->getOAuthEndpoint() . '/invalidate',
            $options
        );

        $token = new Token('', '');
        $this->repository->saveTokens($token);
    }

    public function tryRefreshTokenWithLock(): Token
    {
        if (! $this->lock->acquire()) {
            throw new TokenRefreshInProgressException("token refresh in progress...");
        }

        $token = $this->repository->getTokens();

        if (! $token) {
            throw new \Exception('No refresh token stored');
        }

        try {
            $token = $this->refreshToken();
        } catch (Exception $e) {
            $dt = \DateTime::createFromFormat('U.u', microtime(true));
        } finally {
            $dt = \DateTime::createFromFormat('U.u', microtime(true));
            $this->lock->release();
        }

        return $token;
    }

    public function refreshToken(): Token
    {
        $token = $this->repository->getTokens();

        if (! $token) {
            throw new \Exception('No refresh token stored');
        }

        $accessToken = $token->accessToken;

        $dt = \DateTime::createFromFormat('U.u', microtime(true));
        RLog::info("lock acquired for token - " . $accessToken . ' - ' .  $dt->format("H:i:s.u"));

        $params = array(
            'client_id'     => $this->getApiConfig()->getClientId(),
            'grant_type'    => 'refresh_token',
            'refresh_token' => $token->refreshToken,
        );

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $options = array(
            'body' => http_build_query($params),
            'headers' => $headers,
        );

        $response = $this->httpClient->request(
            'POST',
            $this->getApiConfig()->getOAuthEndpoint() . '/token',
            $options
        );

        $body = $this->httpClient->getBody($response);

        if (empty($body['access_token']) || empty($body['refresh_token'])) {
            throw new \Exception('Refresh response invalid - ' . json_encode($body));
        }

        $newToken = new Token($body['access_token'], $body['refresh_token']);
        $this->repository->saveTokens($newToken);

        $savedTokens = $this->repository->getTokens();
        RLog::info("savedToken - " . $token->accessToken . ' - ' .  $dt->format("H:i:s.u"));

        return $savedTokens;
    }
}
