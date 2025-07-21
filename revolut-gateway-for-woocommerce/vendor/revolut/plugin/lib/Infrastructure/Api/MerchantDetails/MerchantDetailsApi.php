<?php

namespace Revolut\Plugin\Infrastructure\Api\MerchantDetails;

use Revolut\Plugin\Infrastructure\Api\MerchantApiClientInterface;

class MerchantDetailsApi implements MerchantDetailsApiInterface
{
    private $merchantApiClient;

    public function __construct(MerchantApiClientInterface $merchantApiClient)
    {
        $this->merchantApiClient = $merchantApiClient;
    }


    public function getDetails(): array
    {
        return $this->merchantApiClient->get('merchant');
    }

    public function getFeatures(): array
    {
        $details = $this->getDetails();
        return isset($details['features']) ? $details['features'] : [];
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->getFeatures());
    }
}
