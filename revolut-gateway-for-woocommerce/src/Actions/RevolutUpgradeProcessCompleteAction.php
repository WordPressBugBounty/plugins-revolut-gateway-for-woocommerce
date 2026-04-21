<?php
namespace Revolut\Wordpress\Actions;

defined( 'ABSPATH' ) || exit;

use Revolut\Plugin\Services\Config\Api\ConfigInterface;
use Revolut\Plugin\Services\Log\RLog;
use Revolut\Wordpress\Infrastructure\HttpClient;
use Revolut\Plugin\Services\Config\Store\StoreDetailsServiceInterface;

class RevolutUpgradeProcessCompleteAction {

	const UPGRADER_PROCESS_COMPLETE_ACTION = 'upgrader_process_complete';
	const PLUGIN_SLUG                      = 'revolut-gateway-for-woocommerce';
	const PLUGIN_FILE                      = self::PLUGIN_SLUG . '/gateway-revolut.php';
	const API_ENDPOINT                     = 'api/plugins/update-confirm';

	/** @var HttpClient */
	private $http;

	/** @var StoreDetailsServiceInterface */
	private $store_details;

	/** @var string */
	private $api_url;

	public function __construct( HttpClient $http, StoreDetailsServiceInterface $store_details ) {
		$this->http          = $http;
		$this->store_details = $store_details;
		$base_url            = 'https://checkout.revolut.com/';
		$this->api_url       = $base_url . self::API_ENDPOINT;
	}

	public function register(): void {
		add_action( self::UPGRADER_PROCESS_COMPLETE_ACTION, array( $this, 'run' ), 10, 2 );
	}

	/**
	 * @param mixed $upgrader
	 * @param mixed $options
	 * @return void
	 */
	public function run( $upgrader, $options ): void {
		try {
			if ( ! is_object( $upgrader ) || ! is_array( $options ) ) {
				return;
			}

			if ( ! array_key_exists( 'type', $options ) || ! array_key_exists( 'action', $options ) || ! array_key_exists( 'plugins', $options ) ) {
				return;
			}

			$is_plugin_update = 'plugin' === $options['type'] && 'update' === $options['action'];

			if ( ! $is_plugin_update ) {
				return;
			}


			if ( in_array( self::PLUGIN_FILE, $options['plugins'], true ) ) {
				$this->confirm_plugin_update();
			}
		} catch ( \Throwable $e ) {
			RLog::error( 'Revolut plugin upgrade confirmation failed: ' . $e->getMessage() );
		}
	}

	public function confirm_plugin_update(): void {
		try {
			$site_id   = get_current_blog_id();
			$site_path = is_multisite() ? get_site_url( $site_id ) : site_url();
			$domain    = $this->store_details->getStoreDomain();
			$platform  = 'WOOCOMMERCE';

			$version = $this->get_plugin_version();

			if ( ! $version ) {
				throw new \Exception( 'Couldnt retrieve updated version, will not be sent' );
			}

			$params = array(
				'domain'    => $domain,
				'site_id'   => $site_id,
				'site_path' => $site_path,
				'platform'  => $platform,
				'version'   => $version,
			);

			$this->http->request(
				'POST',
				$this->api_url,
				array(
					'headers' => array( 'Content-Type' => 'application/json' ),
					'body'    => wp_json_encode( $params ),
				)
			);

		} catch ( \Throwable $e ) {
			RLog::error( 'Revolut plugin upgrade confirmation failed: ' . $e->getMessage() );
		}
	}

	protected function get_plugin_version(): string {
		$data = get_plugin_data( WP_PLUGIN_DIR . '/' . self::PLUGIN_FILE, false, false );
		return $data['Version'];
	}
}
