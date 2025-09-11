<?php

namespace Revolut\Plugin\Services\Config\Store;

interface StoreDetailsServiceInterface
{
    public function getStoreDomain(): string;
    public function getStoreWebhookEndpoint(): string;
    public function getStoreCurrency(): string;
}
