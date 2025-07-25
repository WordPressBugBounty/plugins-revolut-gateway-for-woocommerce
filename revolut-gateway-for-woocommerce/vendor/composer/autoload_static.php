<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit91a2c1deb4ee1f93baf70cfbce521801
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'Revolut\\Wordpress\\' => 18,
            'Revolut\\Plugin\\' => 15,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Revolut\\Wordpress\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Revolut\\Plugin\\' => 
        array (
            0 => __DIR__ . '/..' . '/revolut/plugin/lib',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Revolut\\Plugin\\Domain\\Model\\Token' => __DIR__ . '/..' . '/revolut/plugin/lib/Domain/Model/Token.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\ApplePay\\ApplePayApi' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/ApplePay/ApplePayApi.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\ApplePay\\ApplePayApiInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/ApplePay/ApplePayApiInterface.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\Auth\\AccessTokenAuthStrategy' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/Auth/AccessTokenAuthStrategy.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\Auth\\AuthStrategyFactory' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/Auth/AuthStrategyFactory.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\Auth\\AuthStrategyInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/Auth/AuthStrategyInterface.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\Auth\\PublicKeyAuthStrategy' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/Auth/PublicKeyAuthStrategy.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\Auth\\SecretKeyAuthStrategy' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/Auth/SecretKeyAuthStrategy.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\MerchantApi' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/MerchantApi.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\MerchantApiClient' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/MerchantApiClient.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\MerchantApiClientInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/MerchantApiClientInterface.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\MerchantDetails\\MerchantDetailsApi' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/MerchantDetails/MerchantDetailsApi.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\MerchantDetails\\MerchantDetailsApiInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/MerchantDetails/MerchantDetailsApiInterface.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\Webhooks\\WebhooksApi' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/Webhooks/WebhooksApi.php',
        'Revolut\\Plugin\\Infrastructure\\Api\\Webhooks\\WebhooksApiInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Api/Webhooks/WebhooksApiInterface.php',
        'Revolut\\Plugin\\Infrastructure\\Config\\Api\\Config' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Config/Api/Config.php',
        'Revolut\\Plugin\\Infrastructure\\Config\\Api\\DevConfig' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Config/Api/DevConfig.php',
        'Revolut\\Plugin\\Infrastructure\\Config\\Api\\Environment' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Config/Api/Environment.php',
        'Revolut\\Plugin\\Infrastructure\\Config\\Api\\ProdConfig' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Config/Api/ProdConfig.php',
        'Revolut\\Plugin\\Infrastructure\\Config\\Api\\SandboxConfig' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Config/Api/SandboxConfig.php',
        'Revolut\\Plugin\\Infrastructure\\FileSystem\\FileSystemInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/FileSystem/FileSystemInterface.php',
        'Revolut\\Plugin\\Infrastructure\\Lock\\LockService' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Lock/LockService.php',
        'Revolut\\Plugin\\Infrastructure\\Lock\\TokenRefreshJobLockService' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Lock/TokenRefreshJobLockService.php',
        'Revolut\\Plugin\\Infrastructure\\Lock\\TokenRefreshLockService' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Lock/TokenRefreshLockService.php',
        'Revolut\\Plugin\\Infrastructure\\Repositories\\OptionTokenRepository' => __DIR__ . '/..' . '/revolut/plugin/lib/Infrastructure/Repositories/OptionTokenRepository.php',
        'Revolut\\Plugin\\Presentation\\PostInstallSetupResourceInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Presentation/PostInstallSetupResourceInterface.php',
        'Revolut\\Plugin\\Services\\ApplePay\\ApplePayOnboardingInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/ApplePay/ApplePayOnboardingInterface.php',
        'Revolut\\Plugin\\Services\\ApplePay\\ApplePayOnboardingService' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/ApplePay/ApplePayOnboardingService.php',
        'Revolut\\Plugin\\Services\\AuthConnect\\AuthConnect' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/AuthConnect/AuthConnect.php',
        'Revolut\\Plugin\\Services\\AuthConnect\\AuthConnectResourceContract' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/AuthConnect/AuthConnectResourceContract.php',
        'Revolut\\Plugin\\Services\\AuthConnect\\AuthConnectServiceInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/AuthConnect/AuthConnectServiceInterface.php',
        'Revolut\\Plugin\\Services\\AuthConnect\\Exceptions\\TokenRefreshInProgressException' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/AuthConnect/Exceptions/TokenRefreshInProgressException.php',
        'Revolut\\Plugin\\Services\\AuthConnect\\TokenRefreshServiceInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/AuthConnect/TokenRefreshServiceInterface.php',
        'Revolut\\Plugin\\Services\\Config\\Api\\ConfigInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Config/Api/ConfigInterface.php',
        'Revolut\\Plugin\\Services\\Config\\Api\\ConfigProviderInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Config/Api/ConfigProviderInterface.php',
        'Revolut\\Plugin\\Services\\Config\\Store\\StoreDetails' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Config/Store/StoreDetails.php',
        'Revolut\\Plugin\\Services\\Config\\Store\\StoreDetailsAdapterInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Config/Store/StoreDetailsAdapterInterface.php',
        'Revolut\\Plugin\\Services\\Config\\Store\\StoreDetailsInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Config/Store/StoreDetailsInterface.php',
        'Revolut\\Plugin\\Services\\Http\\HttpClientInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Http/HttpClientInterface.php',
        'Revolut\\Plugin\\Services\\Http\\HttpResourceInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Http/HttpResourceInterface.php',
        'Revolut\\Plugin\\Services\\Lock\\LockInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Lock/LockInterface.php',
        'Revolut\\Plugin\\Services\\Log\\LoggerInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Log/LoggerInterface.php',
        'Revolut\\Plugin\\Services\\Log\\RLog' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Log/RLog.php',
        'Revolut\\Plugin\\Services\\Repositories\\OptionRepositoryInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Repositories/OptionRepositoryInterface.php',
        'Revolut\\Plugin\\Services\\Repositories\\TokenRepositoryInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Repositories/TokenRepositoryInterface.php',
        'Revolut\\Plugin\\Services\\Webhooks\\WebhooksEvents' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Webhooks/WebhooksEvents.php',
        'Revolut\\Plugin\\Services\\Webhooks\\WebhooksInterface' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Webhooks/WebhooksInterface.php',
        'Revolut\\Plugin\\Services\\Webhooks\\WebhooksService' => __DIR__ . '/..' . '/revolut/plugin/lib/Services/Webhooks/WebhooksService.php',
        'Revolut\\Wordpress\\Infrastructure\\AuthConnectJob' => __DIR__ . '/../..' . '/src/Infrastructure/AuthConnectJob.php',
        'Revolut\\Wordpress\\Infrastructure\\Config\\ApiConfigProvider' => __DIR__ . '/../..' . '/src/Infrastructure/Config/ApiConfigProvider.php',
        'Revolut\\Wordpress\\Infrastructure\\Config\\StoreDetailsAdapter' => __DIR__ . '/../..' . '/src/Infrastructure/Config/StoreDetailsAdapter.php',
        'Revolut\\Wordpress\\Infrastructure\\FileSystemAdapter' => __DIR__ . '/../..' . '/src/Infrastructure/FileSystemAdapter.php',
        'Revolut\\Wordpress\\Infrastructure\\HttpClient' => __DIR__ . '/../..' . '/src/Infrastructure/HttpClient.php',
        'Revolut\\Wordpress\\Infrastructure\\Logger' => __DIR__ . '/../..' . '/src/Infrastructure/Logger.php',
        'Revolut\\Wordpress\\Infrastructure\\OptionRepository' => __DIR__ . '/../..' . '/src/Infrastructure/OptionRepository.php',
        'Revolut\\Wordpress\\Presentation\\AuthConnectResource' => __DIR__ . '/../..' . '/src/Presentation/AuthConnectResource.php',
        'Revolut\\Wordpress\\Presentation\\PostInstallSetupResource' => __DIR__ . '/../..' . '/src/Presentation/PostInstallSetupResource.php',
        'Revolut\\Wordpress\\ServiceProvider' => __DIR__ . '/../..' . '/src/ServiceProvider.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit91a2c1deb4ee1f93baf70cfbce521801::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit91a2c1deb4ee1f93baf70cfbce521801::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit91a2c1deb4ee1f93baf70cfbce521801::$classMap;

        }, null, ClassLoader::class);
    }
}
