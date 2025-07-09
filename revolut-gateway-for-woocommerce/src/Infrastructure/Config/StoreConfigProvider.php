<?php

namespace Revolut\Wordpress\Infrastructure\Config;

use Revolut\Plugin\Services\Config\Api\ConfigInterface;
use Revolut\Plugin\Services\Config\Store\StoreConfigInterface;

class StoreConfigProvider implements StoreConfigInterface {

    private $apiConfig;
    public function __construct(ConfigInterface $apiConfig)
    {
        $this->apiConfig = $apiConfig;
    }

    public function getStoreDomain()
    {
        if(isset( $_SERVER['HTTP_HOST'] )) {
            return sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) );
        }

        return str_replace(
                    array(
                        'https://',
                        'http://',
		            ),
			        '',
		            get_site_url()
	            );
    }

    public function getStoreWebhookEndpoint()
    {
        return get_site_url( null, '/wp-json/wc/v3/revolut/webhook/' . $this->apiConfig->getMode(), 'https' );
    }
}