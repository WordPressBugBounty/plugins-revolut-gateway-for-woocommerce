<?php

namespace Revolut\Plugin\Services\Config\Store;

interface StoreDetailsInterface
{
    public function getStoreDomain(): string;
    public function getStoreWebhookEndpoint(): string;
    public function getStoreCurrency(): string;
    public function getStoreLegalCountryCode(): string;
    public function getStoreFeatures(): array;
}
