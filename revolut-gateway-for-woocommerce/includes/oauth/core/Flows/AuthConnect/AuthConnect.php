<?php

namespace Revolut\Plugin\Core\Flows\AuthConnect;

use Exception;
use  Revolut\Plugin\Core\Models\Token;
use  Revolut\Plugin\Core\Interfaces\Services\LockInterface;
use  Revolut\Plugin\Core\Infrastructure\ConfigProvider;
use  Revolut\Plugin\Core\Exceptions\TokenRefreshInProgressException;
use  Revolut\Plugin\Core\Infrastructure\TokenRepositoryInterface;
use  Revolut\Plugin\Core\Infrastructure\HttpClientInterface;
use  Revolut\Plugin\Core\Services\RLog;

class AuthConnect
{
    private $repository;
    private $httpClient;
    private $configProvider;
    private $lock;
    private $tokenRefreshJobLock;

    public function __construct( 
        TokenRepositoryInterface $repo, 
        HttpClientInterface $httpClient, 
        ConfigProvider $provider, 
        LockInterface $lock,
        ?LockInterface $tokenRefreshJobLock = null,
    ) {
        $this->repository            = $repo;
        $this->httpClient            = $httpClient;
        $this->configProvider        = $provider;
        $this->lock                  = $lock;
        $this->tokenRefreshJobLock   = $tokenRefreshJobLock;
    }

    public function exchangeAuthorizationCode( $mode, $code, $verifier )
    {
        $params = array(
            'grant_type'    => 'authorization_code',
            'code'          => $code,
            'code_verifier' => $verifier,
            'client_id'     => $this->configProvider->getAuthAppClientId($mode),
        );

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $body = $this->httpClient->post(
            $this->configProvider->getAuthTokenEndpoint($mode) . '/token', 
            http_build_query($params),
            $headers
        );

        if (empty($body['access_token']) || empty($body['refresh_token'])) {
            throw new \Exception('Invalid Token response  - ' . json_encode($body));
        }

        $token = new Token($body['access_token'], $body['refresh_token']);
        $this->repository->saveTokens($token);
        return $token;
    }

    public function disconnect( $mode )
    {
        $params = array(
            'client_id'     => $this->configProvider->getAuthAppClientId($mode),
        );

        $token = $this->repository->getTokens();

        $headers = array(
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $token->accessToken,
        );

        $this->httpClient->post(
            $this->configProvider->getAuthTokenEndpoint($mode) . '/invalidate', 
            json_encode($params),
            $headers
        );

        $token = new Token('', '');
        $this->repository->saveTokens($token);
    }


    public function requestRefresh($mode)
    {
        $token = $this->repository->getTokens();

        if (! $token) {
            throw new \Exception('No refresh token stored');
        }

        $accessToken = $token->accessToken;
        
        $dt = \DateTime::createFromFormat('U.u', microtime(true));
        RLog::info("lock acquired for token - " . $accessToken . ' - ' .  $dt->format("H:i:s.u"));

        $params = array(
            'client_id'     => $this->configProvider->getAuthAppClientId($mode),
            'grant_type'    => 'refresh_token',
            'refresh_token' => $token->refreshToken,
        );

        $headers = array(
            'Content-Type' => 'application/x-www-form-urlencoded',
        );

        $body = $this->httpClient->post(
            $this->configProvider->getAuthTokenEndpoint($mode) . '/token', 
            http_build_query($params),
            $headers
        );
                
        if (empty($body['access_token']) || empty($body['refresh_token'])) {
            throw new \Exception('Refresh response invalid - ' . json_encode($body));
        }

        $newToken = new Token($body['access_token'], $body['refresh_token']);
        $this->repository->saveTokens($newToken);
   
        $savedTokens = $this->repository->getTokens();
        RLog::info("savedToken - " . $token->accessToken . ' - ' .  $dt->format("H:i:s.u"));

        return $savedTokens;
    }

    public function refreshToken($mode)
    {
        if (! $this->lock->acquire()) {
            throw new TokenRefreshInProgressException("token refresh in progress...");
        }

        $token = $this->repository->getTokens();

        if (! $token) {
            throw new \Exception('No refresh token stored');
        }

        try {
            $this->requestRefresh($mode);
        }catch (Exception $e){
            $dt = \DateTime::createFromFormat('U.u', microtime(true));
            RLog::error("refreshToken error - " . $e->getMessage() . ' - ' .  $dt->format("H:i:s.u"));
        } finally {
            $dt = \DateTime::createFromFormat('U.u', microtime(true));
            RLog::info("release lock for - " . $token->accessToken . ' - ' .  $dt->format("H:i:s.u"));
            $this->lock->release();
        }
    }

    public function refreshTokenJob($mode)
    {
        if (! $this->tokenRefreshJobLock->acquire()) {
            throw new TokenRefreshInProgressException("token refresh in progress...");
        }

        try {
            RLog::info("refresh_token job start.");
            $this->requestRefresh($mode);
            RLog::info("refresh_token job completed.");
        } catch (Exception $e){
            $dt = \DateTime::createFromFormat('U.u', microtime(true));
            RLog::error("refreshToken error - " . $e->getMessage() . ' - ' .  $dt->format("H:i:s.u"));
        } finally {
            //job should run in every 9 minutes
            //don't release lock for refresh job.
            //lock should prevent all refresh attempts during 10 minutes
            //lock will be released automatically after 10 minutes            
        }
    }
}
