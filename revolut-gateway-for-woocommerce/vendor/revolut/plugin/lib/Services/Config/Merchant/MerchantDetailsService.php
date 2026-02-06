<?php

namespace Revolut\Plugin\Services\Config\Merchant;

use Revolut\Plugin\Services\Config\Api\ConfigInterface;
use Revolut\Plugin\Infrastructure\Api\MerchantDetails\MerchantDetailsApiInterface;
use Revolut\Plugin\Services\Config\Store\StoreDetailsServiceInterface;
use Revolut\Plugin\Services\Repositories\OptionRepositoryInterface;
use Revolut\Plugin\Services\Config\Merchant\MerchantDetailsServiceInterface;

class MerchantDetailsService implements MerchantDetailsServiceInterface
{
    public const AVAILABLE_PAYMENT_METHODS_CACHE_TTL_IN_SECONDS = 86400;
    private $merchantDetails;
    private $repo;
    private $apiConfig;
    private $storeDetails;

    private $availablePaymentMethods = [];
    private $merchantFeatures = [];

    public function __construct(OptionRepositoryInterface $repo, MerchantDetailsApiInterface $merchantDetails, ConfigInterface $apiConfig, StoreDetailsServiceInterface $storeDetails)
    {
        $this->merchantDetails = $merchantDetails;
        $this->repo = $repo;
        $this->apiConfig = $apiConfig;
        $this->storeDetails = $storeDetails;
    }

    public function getMerchantFeatures(): array
    {
        if (empty($this->merchantFeatures)) {
            $this->merchantFeatures = $this->merchantDetails->getFeatures();
        }
        return $this->merchantFeatures;
    }

    public function hasFeature(string $feature): bool
    {
        return in_array($feature, $this->getMerchantFeatures()());
    }

    public function getAvailablePaymentMethods(?int $amount = null, ?string $currency = null): array
    {
        if (!isset($amount)) {
            $amount = 0;
        }

        if (!isset($currency)) {
            $currency = $this->storeDetails->getStoreCurrency();
        }

        if (!empty($this->availablePaymentMethods)) {
            return $this->availablePaymentMethods;
        }

        $cacheKey = $this->keyAvailablePaymentMethods($currency);

        $this->availablePaymentMethods = $this->repo->getCached($cacheKey);

        if (empty($this->availablePaymentMethods)) {
            $this->availablePaymentMethods = $this->merchantDetails->availablePaymentMethods($amount, $currency);
            if (!empty($this->availablePaymentMethods)) {
                $this->repo->addCached($cacheKey, $this->availablePaymentMethods, self::AVAILABLE_PAYMENT_METHODS_CACHE_TTL_IN_SECONDS);
            }
        }

        return $this->availablePaymentMethods;
    }

    public function setupMerchantPublicKey(): void
    {
        $publicKey = $this->merchantDetails->getPublicKey();
        if (empty($publicKey)) {
            throw new \Exception("Unable to retrieve merchant public key");
        }

        $this->repo->update($this->keyMerchantPublicKey(), $publicKey);
    }

    private function keyMerchantPublicKey()
    {
        $mode = $this->apiConfig->getMode();
        return "{$mode}_revolut_merchant_public_key";
    }

    private function keyAvailablePaymentMethods(string $currency)
    {
        $mode = $this->apiConfig->getMode();
        return "available_payment_methods_{$mode}_{$currency}";
    }
}
