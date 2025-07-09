<?php

namespace Revolut\Plugin\Infrastructure\Config\Api;

use Revolut\Plugin\Infrastructure\Config\Api\Environment;
use Revolut\Plugin\Infrastructure\Config\Api\Config;

class ProdConfig extends Config
{
    protected $mode = Environment::PROD;
    protected $clientId = '9cda975e-016c-4b49-b5c6-37d1285ba046';
    protected $baseUrl = 'https://merchant.revolut.com';
    protected $connectServerUrl = 'https://checkout.revolut.com/api/connect';
}
