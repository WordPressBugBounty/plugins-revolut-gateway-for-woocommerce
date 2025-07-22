<?php

namespace Revolut\Wordpress\Infrastructure\Config;

use Revolut\Plugin\Services\Config\Store\StoreDetailsAdapterInterface;

class StoreDetailsAdapter implements StoreDetailsAdapterInterface
{

    public function getStoreCurrency(): string
    {
        return get_woocommerce_currency();
    }

    public function getStoreDomain(): string
    {  
        $site_url = get_site_url(); 
        return parse_url($site_url, PHP_URL_HOST);
    }

    public function getStoreWebhookEndpoint(): string
    {
        return '/wp-json/wc/v3/revolut/webhook/';
    }
}