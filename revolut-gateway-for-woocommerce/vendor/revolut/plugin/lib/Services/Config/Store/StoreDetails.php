<?php

namespace Revolut\Plugin\Services\Config\Store;

use Revolut\Plugin\Infrastructure\Api\MerchantDetails\MerchantDetailsApiInterface;
use Revolut\Plugin\Services\Config\Api\ConfigInterface;
use Revolut\Plugin\Services\Repositories\OptionRepositoryInterface;

class StoreDetails implements StoreDetailsInterface
{
    private $storeDetailsAdapter;
    private $merchantDetails;
    private $repo;
    private $config;

    public function __construct(
        StoreDetailsAdapterInterface $storeDetailsAdapter,
        MerchantDetailsApiInterface $merchantDetails,
        ConfigInterface $config,
        OptionRepositoryInterface $repo
    ) {
        $this->storeDetailsAdapter = $storeDetailsAdapter;
        $this->merchantDetails = $merchantDetails;
        $this->repo = $repo;
        $this->config = $config;
    }

    public function getStoreFeatures(): array
    {
        return $this->merchantDetails->getFeatures();
    }

    public function getStoreDomain(): string
    {
        return $this->storeDetailsAdapter->getStoreDomain();
    }

    public function getStoreWebhookEndpoint(): string
    {
        return $this->storeDetailsAdapter->getStoreWebhookEndpoint();
    }

    public function getStoreCurrency(): string
    {
        return $this->storeDetailsAdapter->getStoreCurrency();
    }

    public function getStoreLegalCountryCode(): string
    {
        $mode = $this->config->getMode();

        $legalCountryOptionKey = $mode . '_merchant_legal_country';

        $cachedValue = $this->repo->get($legalCountryOptionKey);

        if ($cachedValue) {
            return $cachedValue;
        }

        $details = $this->merchantDetails->getDetails();

        if (isset($details['legal_country'])) {
            $this->repo->add($legalCountryOptionKey, $details['legal_country']);
            return $details['legal_country'];
        }

        return '';
    }
}
