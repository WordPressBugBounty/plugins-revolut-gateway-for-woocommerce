<?php

namespace Revolut\Plugin\Services\Config\Store;

interface StoreDetailsInterface
{
    public function getStoreDomain(): string;
    public function getStoreWebhookEndpoint(): string;
    public function getStoreCurrency(): string;
    public function getStoreFeatures(): array;
    public function getAvailablePaymentMethods(?int $amount = null, ?string $currency = null): array;
}
