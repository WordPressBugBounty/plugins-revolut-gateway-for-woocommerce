<?php

namespace Revolut\Plugin\Bootstrap;

use Revolut\Plugin\Core\Infrastructure\ConfigProvider;
use Revolut\Plugin\Core\Services\TokenRefreshJobLockService;
use Revolut\Plugin\Core\Services\TokenRefreshLockService;
use Revolut\Plugin\Core\Flows\AuthConnect\AuthConnect;
use Revolut\Plugin\Infrastructure\Wordpress\HttpClient;
use Revolut\Plugin\Infrastructure\Wordpress\OptionRepository;
use Revolut\Plugin\Infrastructure\Wordpress\OptionTokenRepository;
use Revolut\Plugin\Infrastructure\Wordpress\AuthConnectJob;
use Revolut\Plugin\Core\Interfaces\Services\LockInterface;
use Revolut\Plugin\Presentation\AuthConnectResource;

class ServiceProvider
{
    public static function authConnectService()
    {
        return new AuthConnect(
            new OptionTokenRepository(
                new OptionRepository()
            ),
            new HttpClient(),
            new ConfigProvider(),
            new TokenRefreshLockService(new OptionRepository()),
            new TokenRefreshJobLockService(new OptionRepository())
        );
    }

    public static function authConnectJob()
    {
        return new AuthConnectJob(
            self::authConnectService()
        );
    }
    
    public static function authConnectResource()
    {
        return new AuthConnectResource(
            self::authConnectService()
        );
    }
}
