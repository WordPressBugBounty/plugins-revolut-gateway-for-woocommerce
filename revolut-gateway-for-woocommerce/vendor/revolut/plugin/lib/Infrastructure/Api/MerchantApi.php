<?php

namespace Revolut\Plugin\Infrastructure\Api;

use Revolut\Plugin\Infrastructure\Api\ApplePay\ApplePayApi;
use Revolut\Plugin\Infrastructure\Api\MerchantApiClient;
use Revolut\Plugin\Services\Http\HttpClientInterface;
use Revolut\Plugin\Infrastructure\Api\Auth\AuthStrategyFactory;
use Revolut\Plugin\Infrastructure\Api\Webhooks\WebhooksApi;

// Facade class for Merchant Api

final class MerchantApi
{
    private static $instance = null;

    private $privateLegacyClient;
    private $privateClient;
    private $publicClient;

    private function __construct(
        AuthStrategyFactory $authStrategyFactory,
        HttpClientInterface $http
    ) {
        $this->privateClient = new MerchantApiClient($authStrategyFactory->createPrivateAuthStrategy(), $http);
        $this->privateLegacyClient = new MerchantApiClient(
            $authStrategyFactory->createPrivateAuthStrategy(),
            $http,
            true
        );
        $this->publicClient = new MerchantApiClient($authStrategyFactory->createPublicAuthStrategy(), $http);
    }

    public static function init(
        AuthStrategyFactory $authStrategyFactory,
        HttpClientInterface $http
    ): void {
        self::$instance = new self($authStrategyFactory, $http);
    }

    private static function instance(): self
    {
        if (!self::$instance) {
            throw new \RuntimeException('RevolutApiFacade::init() must be called first.');
        }

        return self::$instance;
    }

    public static function private(): MerchantApiClient
    {
        return self::instance()->privateClient;
    }

    public static function privateLegacy(): MerchantApiClient
    {
        return self::instance()->privateLegacyClient;
    }

    public static function public(): MerchantApiClient
    {
        return self::instance()->publicClient;
    }

    public static function applePay()
    {
        return new ApplePayApi(self::private());
    }

    public static function webhooks()
    {
        return new WebhooksApi(self::privateLegacy());
    }
}
