<?php

namespace Revolut\Wordpress;

use Revolut\Wordpress\Infrastructure\FileSystemAdapter;
use Revolut\Wordpress\Infrastructure\HttpClient;
use Revolut\Wordpress\Infrastructure\OptionRepository;
use Revolut\Wordpress\Infrastructure\Config\ApiConfigProvider;
use Revolut\Wordpress\Infrastructure\AuthConnectJob;

use Revolut\Wordpress\Presentation\AuthConnectResource;
use Revolut\Wordpress\Presentation\PostInstallSetupResource;

use Revolut\Plugin\Infrastructure\Lock\TokenRefreshJobLockService;
use Revolut\Plugin\Infrastructure\Lock\TokenRefreshLockService;
use Revolut\Plugin\Infrastructure\Lock\LockService;
use Revolut\Plugin\Services\AuthConnect\AuthConnect;

use Revolut\Plugin\Infrastructure\Repositories\OptionTokenRepository;
use Revolut\Plugin\Infrastructure\Config\Api\Config;
use Revolut\Plugin\Infrastructure\Api\Auth\AccessTokenAuthStrategy;
use Revolut\Plugin\Infrastructure\Api\Auth\AuthStrategyFactory;
use Revolut\Plugin\Infrastructure\Api\MerchantApi;
use Revolut\Plugin\Services\ApplePay\ApplePayOnboardingService;
use Revolut\Plugin\Services\Config\Merchant\MerchantDetailsService;
use Revolut\Plugin\Services\Config\Merchant\MerchantDetailsServiceInterface;
use Revolut\Plugin\Services\Config\Store\StoreDetailsService;
use Revolut\Plugin\Services\Config\Store\StoreDetailsServiceInterface;
use Revolut\Plugin\Services\Webhooks\WebhooksService;
use Revolut\Wordpress\Infrastructure\Config\StoreDetailsAdapter;

class ServiceProvider
{
    public static $configProviderInstance = null;

    public static function resetApiConfigProvider()
    {   
        self::$configProviderInstance = new ApiConfigProvider(self::optionRepository(), self::optionTokenRepository());
    }
    
    public static function apiConfigProvider(): ApiConfigProvider
    {   
        if(self::$configProviderInstance != null){
            return self::$configProviderInstance;
        }

        self::$configProviderInstance = new ApiConfigProvider(self::optionRepository(), self::optionTokenRepository());
        
        return self::$configProviderInstance;
    }

    public static function applePayOnboardingService() {
        return new ApplePayOnboardingService(
            new FileSystemAdapter(),
            MerchantApi::applePay()
        );
    }

    public static function postInstallSetupResource() {
        return new PostInstallSetupResource(
            self::storeDetailsService(),
            self::merchantDetailsService(),
            self::applePayOnboardingService(), 
            self::webhooksService(), 
        );
    }

    public static function webhooksService() {
        return new WebhooksService(
            MerchantApi::webhooks(), 
            self::optionRepository(), 
            self::apiConfig()
        );
    }

    public static function storeDetailsService(): StoreDetailsServiceInterface
    {
        return new StoreDetailsService(
            new StoreDetailsAdapter(),
            self::apiConfig(),
        );
    }

    public static function merchantDetailsService(): MerchantDetailsServiceInterface
    {
        return new MerchantDetailsService(
            self::optionRepository(), 
            MerchantApi::merchantDetails(), 
            self::apiConfig(), 
            self::storeDetailsService()
        );
    }

    public static function apiConfig(): Config
    {
        return self::apiConfigProvider()->getConfig();
    }

    public static function optionRepository(): OptionRepository
    {
        return new OptionRepository();
    }
    
    public static function optionTokenRepository(): OptionTokenRepository
    {
        return new OptionTokenRepository(self::optionRepository());
    }

    public static function tokenRefreshLockService(): TokenRefreshLockService
    {
        return new TokenRefreshLockService(self::optionRepository());
    }

    public static function tokenRefreshJobLockService(): TokenRefreshJobLockService
    {
        return new TokenRefreshJobLockService(self::optionRepository());
    }
    
    public static function httpClient(): HttpClient
    {
        return new HttpClient();
    }

    public static function accessTokenAuthStrategy(): AccessTokenAuthStrategy
    {
        return new AccessTokenAuthStrategy(
            self::tokenRefreshLockService(),
            self::apiConfigProvider(),
            self::optionTokenRepository(),
            self::authConnectService()
        );
    }

    public static function authStrategyFactory(): AuthStrategyFactory
    {
        return new AuthStrategyFactory(
            self::apiConfigProvider(),
            self::tokenRefreshLockService(),
            self::optionTokenRepository(),
            self::authConnectService()
        );
    }

    public static function authConnectService(): AuthConnect
    {
        return new AuthConnect(
            self::optionTokenRepository(),
            self::httpClient(),
            self::apiConfigProvider(),
            self::tokenRefreshLockService()
        );
    }

    public static function authConnectResource(): AuthConnectResource
    {
        return new AuthConnectResource(
            self::authConnectService()
        );
    }

    public static function authConnectJob(): AuthConnectJob
    {
        return new AuthConnectJob(
            self::tokenRefreshJobLockService(),
            self::authConnectService(),
            self::apiConfigProvider()
        );
    }

    public static function processSubscriptionPaymentLock($key, $timeout = 30): LockService
    {
        return new LockService(
            self::optionRepository(),
            strtoupper("revolut_process_subscription_payment_lock_{$key}"),
            $timeout
        );
    }

    public static function processCapturedOrderLock($key, $timeout = 30): LockService
    {
        return new LockService(
            self::optionRepository(),
            strtoupper("revolut_process_captured_order_lock_{$key}"),
            $timeout
        );
    }

    public static function processAuthorisedOrderLock($key, $timeout = 30): LockService
    {
        return new LockService(
            self::optionRepository(),
            strtoupper("revolut_process_authorised_order_lock_{$key}"),
            $timeout
        );
    }

    public static function capturePaymentLock($key, $timeout = 30): LockService
    {
        return new LockService(
            self::optionRepository(),
            strtoupper("revolut_capture_payment_lock_{$key}"),
            $timeout
        );
    }
    
    public static function initMerchantApi(): void
    {
        MerchantApi::init(
            self::authStrategyFactory(),
            self::httpClient()
        );
    }
}
