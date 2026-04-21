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
        $site_id  = get_current_blog_id();
		$site_url = is_multisite() ? get_site_url( $site_id ) : site_url();
        return parse_url($site_url, PHP_URL_HOST);
    }

    public function getStoreWebhookEndpoint(): string
    {
        return '/wp-json/wc/v3/revolut/webhook/';
    }
}