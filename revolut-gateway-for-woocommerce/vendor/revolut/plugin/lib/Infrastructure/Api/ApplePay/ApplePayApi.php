<?php

namespace Revolut\Plugin\Infrastructure\Api\ApplePay;

use Revolut\Plugin\Infrastructure\Api\MerchantApiClientInterface;

class ApplePayApi implements ApplePayApiInterface
{
    private $merchantApiClient;

    public function __construct(MerchantApiClientInterface $merchantApiClient)
    {
        $this->merchantApiClient = $merchantApiClient;
    }

    public function registerDomain(string $domain)
    {
        $this->merchantApiClient->post('apple-pay/domains/register', ["domain" => $domain]);
    }
}
