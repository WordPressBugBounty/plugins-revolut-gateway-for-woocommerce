<?php

namespace Revolut\Plugin\Services\Config\Store;

interface StoreDetailsAdapterInterface
{
    public function getStoreDomain(): string;
    public function getStoreWebhookEndpoint(): string;
    public function getStoreCurrency(): string;
}
