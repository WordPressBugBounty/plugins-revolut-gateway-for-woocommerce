<?php

namespace Revolut\Plugin\Services\Config\Store;

interface StoreConfigInterface
{
    public function getStoreDomain();
    public function getStoreWebhookEndpoint();
}
