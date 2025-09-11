<?php

namespace Revolut\Plugin\Services\Config\Store;

use Revolut\Plugin\Services\Config\Api\ConfigInterface;

class StoreDetailsService implements StoreDetailsServiceInterface
{
    private $storeDetailsAdapter;
    private $apiConfig;

    public function __construct(
        StoreDetailsAdapterInterface $storeDetailsAdapter,
        ConfigInterface $apiConfig
    ) {
        $this->storeDetailsAdapter = $storeDetailsAdapter;
        $this->apiConfig = $apiConfig;
    }


    public function getStoreDomain(): string
    {
        return $this->storeDetailsAdapter->getStoreDomain();
    }


    public function getStoreWebhookEndpoint(): string
    {
        $domain = $this->getStoreDomain();
        $endpoint = rtrim($this->storeDetailsAdapter->getStoreWebhookEndpoint(), '/');
        $endpoint = ltrim($endpoint, '/');
        $mode = $this->apiConfig->getMode();

        return "https://{$domain}/{$endpoint}/{$mode}";
    }

    public function getStoreCurrency(): string
    {
        return $this->storeDetailsAdapter->getStoreCurrency();
    }
}
