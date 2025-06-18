<?php

require_once __DIR__ . '/autoload.php';

use Revolut\Plugin\Core\Services\RLog;
use Revolut\Plugin\Bootstrap\ServiceProvider;

RLog::setLogger(
    new Revolut\Plugin\Infrastructure\Wordpress\Logger(), 
    array( 'source' => 'revolut-gateway-for-woocommerce' )
);

ServiceProvider::authConnectResource()->registerRoutes();
ServiceProvider::authConnectJob()->run();
