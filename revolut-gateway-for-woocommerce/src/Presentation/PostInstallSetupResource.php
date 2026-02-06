<?php

namespace Revolut\Wordpress\Presentation;

use Exception;
use Revolut\Plugin\Services\ApplePay\ApplePayOnboardingService;
use Revolut\Plugin\Services\Http\HttpResourceInterface;
use Revolut\Plugin\Services\Webhooks\WebhooksEvents;
use Revolut\Plugin\Services\Webhooks\WebhooksInterface;
use Revolut\Plugin\Presentation\PostInstallSetupResourceInterface;
use Revolut\Plugin\Services\Config\Store\StoreDetailsServiceInterface;
use Revolut\Plugin\Services\Config\Merchant\MerchantDetailsServiceInterface;
use Revolut\Plugin\Infrastructure\Api\MerchantApi;
use Revolut\Plugin\Services\Log\RLog;
use Revolut\Wordpress\ServiceProvider;

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

	public function setup_revolut_synchronous_webhook() {
		try {
            $mode = ServiceProvider::apiConfigProvider()->getConfig()->getMode();
			$web_hook_url = rest_url("wc/v3/revolut/address/validation/webhook/$mode");

			if ( strpos( $web_hook_url, 'http://localhost' ) !== false ) {
				return;
			}

			$location_id = $this->setup_revolut_location();

            if(empty($location_id)){
                return;
            }

			if ( get_option( 'revolut_pay_synchronous_webhook_domain_' . $mode . '_' . $location_id ) === $web_hook_url ) {
				update_option( 'revolut_' . $mode . '_location_id', $location_id );
				return;
			}

			$body = array(
				'url'         => $web_hook_url,
				'event_type'  => 'fast_checkout.validate_address',
				'location_id' => $location_id,
			);

			$response = MerchantApi::private()->post( '/synchronous-webhooks', $body );

			if ( isset( $response['signing_key'] ) && ! empty( $response['signing_key'] ) ) {
				update_option( 'revolut_' . $mode . '_location_id', $location_id );
				update_option( 'revolut_pay_synchronous_webhook_domain_' . $mode . '_' . $location_id, $web_hook_url );
				update_option( 'revolut_pay_synchronous_webhook_domain_' . $mode . '_signing_key', $response['signing_key'] );
				RLog::info("setup_revolut_synchronous_webhook - Synchronous webhook successfully registered");
				return;
			}
			} catch(Exception $e) {
				RLog::error("setup_revolut_synchronous_webhook error : " . $e->getMessage());
			}
	}

	public function setup_revolut_location() {
		$domain        = get_site_url();
		$location_name = str_replace( array( 'https://', 'http://' ), '', $domain );
		$locations     = MerchantApi::private()->get( '/locations' );
		if ( ! empty( $locations ) ) {
			foreach ( $locations as $location ) {
				if ( isset( $location['name'] ) && $location['name'] === $domain && ! empty( $location['id'] ) ) {
					return $location['id'];
				}
			}
		}

		$body = array(
			'name'    => $location_name,
			'type'    => 'online',
			'details' => array(
				'domain' => $domain,
			),
		);

		$location = MerchantApi::private()->post( '/locations', $body );

		if ( ! isset( $location['id'] ) || empty( $location['id'] ) ) {
            RLog::error("setup_revolut_location - unable to create location for domain $domain - response : " . json_encode($location));
			return null;
		}

		return $location['id'];
	}

    public function handlePostInstallSetup() {
        check_ajax_referer('wc-revolut-post-install-setup-nonce');
        $this->webhookOnboardingSetup();
		$this->setup_revolut_synchronous_webhook();
        $this->applePayOnboardingSetup();
        $this->merchantDetailsService->setupMerchantPublicKey();

    }
}