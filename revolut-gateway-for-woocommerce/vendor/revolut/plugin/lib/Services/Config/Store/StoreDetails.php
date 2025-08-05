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
    private $apiConfig;

    private $availablePaymentMethods = null;
    private $merchantFeatures = null;

    public function __construct(
        StoreDetailsAdapterInterface $storeDetailsAdapter,
        MerchantDetailsApiInterface $merchantDetails,
        ConfigInterface $apiConfig,
        OptionRepositoryInterface $repo
    ) {
        $this->storeDetailsAdapter = $storeDetailsAdapter;
        $this->merchantDetails = $merchantDetails;
        $this->repo = $repo;
        $this->apiConfig = $apiConfig;
    }

    public function getStoreFeatures(): array
    {
        if (empty($merchantFeatures)) {
            $this->merchantFeatures = $this->merchantDetails->getFeatures();
        }
        return $this->merchantFeatures;
    }

    public function getStoreDomain(): string
    {
        return $this->storeDetailsAdapter->getStoreDomain();
    }

    public function getAvailablePaymentMethods(?int $amount = null, ?string $currency = null): array
    {
        if (!isset($amount)) {
            $amount = 0;
        }

        if (!isset($currency)) {
            $currency = $this->getStoreCurrency();
        }

        if (empty($availablePaymentMethods)) {
            $this->availablePaymentMethods = $this->merchantDetails->availablePaymentMethods($amount, $currency);
        }

        return $this->availablePaymentMethods;
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
