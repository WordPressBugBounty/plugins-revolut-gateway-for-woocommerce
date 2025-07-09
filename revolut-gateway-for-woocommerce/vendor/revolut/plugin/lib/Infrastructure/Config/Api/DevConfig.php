<?php

namespace Revolut\Plugin\Infrastructure\Config\Api;

use Revolut\Plugin\Infrastructure\Config\Api\Environment;
use Revolut\Plugin\Infrastructure\Config\Api\ApiConfig;

class DevConfig extends Config
{
    protected $mode = Environment::DEV;
    protected $clientId = '8440f36e-7849-40cb-8cc7-4ed4e16553a9';
    protected $baseUrl = 'https://merchant.revolut.codes';
    protected $connectServerUrl = 'https://checkout.revolut.codes/api/connect';
}
