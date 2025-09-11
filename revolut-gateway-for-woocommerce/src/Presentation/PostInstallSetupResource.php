<?php

namespace Revolut\Wordpress\Presentation;

use Revolut\Plugin\Services\ApplePay\ApplePayOnboardingService;
use Revolut\Plugin\Services\Http\HttpResourceInterface;
use Revolut\Plugin\Services\Webhooks\WebhooksEvents;
use Revolut\Plugin\Services\Webhooks\WebhooksInterface;
use Revolut\Plugin\Presentation\PostInstallSetupResourceInterface;
use Revolut\Plugin\Services\Config\Store\StoreDetailsServiceInterface;
use Revolut\Plugin\Services\Config\Merchant\MerchantDetailsServiceInterface;

class PostInstallSetupResource implements PostInstallSetupResourceInterface, HttpResourceInterface
{
    private $storeDetailsService;
    private $applePayOnboardingService;
    private $webhooksService;
    private $merchantDetailsService;

    function __construct(
        StoreDetailsServiceInterface $storeDetailsService,
        MerchantDetailsServiceInterface $merchantDetailsService,
        ApplePayOnboardingService $applePayOnboardingService,
        WebhooksInterface $webhooksService
        )
    {   
        $this->merchantDetailsService = $merchantDetailsService;
        $this->storeDetailsService = $storeDetailsService;
        $this->applePayOnboardingService = $applePayOnboardingService;
        $this->webhooksService = $webhooksService;
    }

    public function registerRoutes()
    {
        add_action('wp_ajax_post_install_setup', array($this, 'handlePostInstallSetup'));
    }
    private function webhookOnboardingSetup() {

        $events = [
            WebhooksEvents::ORDER_AUTHORISED_EVENT, 
            WebhooksEvents::ORDER_COMPLETED_EVENT
        ];

        $webhookUrl = $this->storeDetailsService->getStoreWebhookEndpoint();

       return $this->webhooksService->registerWebhook($webhookUrl, $events);
    }

    private function applePayOnboardingSetup() {
        $domain = $this->storeDetailsService->getStoreDomain();
        return $this->applePayOnboardingService->onBoardDomain($domain);
    }

    public function handlePostInstallSetup() {
        check_ajax_referer('wc-revolut-post-install-setup-nonce');
        $this->webhookOnboardingSetup();
        $this->applePayOnboardingSetup();
        $this->merchantDetailsService->setupMerchantPublicKey();

    }
}