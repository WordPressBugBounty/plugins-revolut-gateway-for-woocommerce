<?php

namespace Revolut\Wordpress\Infrastructure;

use Revolut\Plugin\Services\Log\RLog;
use Revolut\Plugin\Services\Lock\LockInterface;
use Revolut\Plugin\Services\AuthConnect\Exceptions\TokenRefreshInProgressException;
use Revolut\Plugin\Services\Config\Api\ConfigProviderInterface;
use Revolut\Plugin\Infrastructure\Config\Api\Config;
use Revolut\Plugin\Services\AuthConnect\TokenRefreshServiceInterface;

class AuthConnectJob
{
    private $tokenRefreshJobLock;

    private $tokenRefreshService;

    private $apiConfigProvider;

    function __construct(
        LockInterface $tokenRefreshJobLock,
        TokenRefreshServiceInterface $tokenRefreshService,
        ConfigProviderInterface $apiConfigProvider
    ) {
        $this->tokenRefreshJobLock = $tokenRefreshJobLock;
        $this->tokenRefreshService = $tokenRefreshService;
        $this->apiConfigProvider = $apiConfigProvider;
    }

    public function getConfig(): Config
    {
        return $this->apiConfigProvider->getConfig();
    }

    public function run()
    {
        add_action('init',  array($this, 'handleTokenRefreshJob'));
    }

    public function refreshTokenJob($mode)
    {
        if (! $this->tokenRefreshJobLock->acquire()) {
            throw new TokenRefreshInProgressException("token refresh in progress...");
        }

        try {
            RLog::info("refresh_token job start.");
            $this->tokenRefreshService->refreshToken();
            RLog::info("refresh_token job completed.");
        } catch (\Exception $e){
            $dt = \DateTime::createFromFormat('U.u', microtime(true));
            RLog::error("refreshToken error - " . $e->getMessage() . ' - ' .  $dt->format("H:i:s.u"));
        } finally {
            //job should run in every 9 minutes
            //don't release lock for refresh job.
            //lock should prevent all refresh attempts during 10 minutes
            //lock will be released automatically after 10 minutes            
        }
    }

    public function handleTokenRefreshJob()
    {   
        try {
            $this->refreshTokenJob($this->getConfig()->getMode());
        } catch (\Exception $e) {
            if($e instanceof TokenRefreshInProgressException){
                return;
            }

            RLog::error("refresh_token job error. " . $e->getMessage());
        }
    }
}
