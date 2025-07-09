<?php

require_once __DIR__ . './../vendor/autoload.php';

use Revolut\Plugin\Services\Log\RLog;
use Revolut\Wordpress\ServiceProvider;
use Revolut\Wordpress\Infrastructure\Logger;

RLog::setLogger(
    new Logger(), 
    array( 'source' => 'revolut-gateway-for-woocommerce' )
);

ServiceProvider::initMerchantApi();
ServiceProvider::authConnectResource()->registerRoutes();
ServiceProvider::postInstallSetupResource()->registerRoutes();
ServiceProvider::authConnectJob()->run();
