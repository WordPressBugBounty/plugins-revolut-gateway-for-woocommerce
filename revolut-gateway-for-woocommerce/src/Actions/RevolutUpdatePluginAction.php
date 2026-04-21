<?php
namespace Revolut\Wordpress\Actions;

defined( 'ABSPATH' ) || exit;

use Exception;

use Revolut\Plugin\Services\Log\RLog;
use Revolut\Wordpress\Infrastructure\HttpClient;
use Revolut\Plugin\Services\Config\Store\StoreDetailsServiceInterface;

class RevolutUpdatePluginAction{

	const PLUGIN_SLUG                          = 'revolut-gateway-for-woocommerce';
	const PLUGIN_FILE                          = self::PLUGIN_SLUG . '/gateway-revolut.php';
	const API_ENDPOINT                         = 'api/plugins/update-check';
	const CACHE_KEY                            = 'revolut_update_manager_meta';
	const CACHE_TTL_SECONDES                   = 7200; // 2hours
	const SITE_TRANSIENT_UPDATE_PLUGINS_FILTER = 'site_transient_update_plugins';

	/** @var HttpClient */
	private $api_client;

	/** @var StoreDetailsServiceInterface */
	private $store_details;

	/** @var string */
	private $api_url;

	public function __construct( HttpClient $api_client, StoreDetailsServiceInterface $store_details ) {
		$this->api_client    = $api_client;
		$this->store_details = $store_details;
		$base_url            = 'https://checkout.revolut.com/';
		$this->api_url       = $base_url . self::API_ENDPOINT;
	}

	public function register(): void {
		add_filter( self::SITE_TRANSIENT_UPDATE_PLUGINS_FILTER, array( $this, 'filter_transient' ) );
	}

	/**
	 * @param mixed $transient
	 * @return mixed
	 */
	public function filter_transient( $transient ) {
		try {

			if ( ! is_object( $transient ) ) {
				return $transient;
			}

			if ( empty( $transient->checked[ self::PLUGIN_FILE ] ) ) {
				return $transient;
			}

			$current_version = $transient->checked[ self::PLUGIN_FILE ];
			$wporg_update    = $transient->response[ self::PLUGIN_FILE ] ?? null;
			$meta            = $this->fetch_meta();

			if ( ! $meta ) {
				return $transient;
			}

			if ( $this->should_rollback( $current_version, $meta ) ) {
				$transient->response[ self::PLUGIN_FILE ] = $this->update_obj( $meta['version'], $meta['download_url'] );
				return $transient;
			}

			if ( ! $this->should_apply_beta( $current_version, $meta, $wporg_update ) ) {
				return $transient;
			}

			if ( empty( $transient->response ) || ! is_array( $transient->response ) ) {
				$transient->response = array();
			}
			$transient->response[ self::PLUGIN_FILE ] = $this->update_obj( $meta['version'], $meta['download_url'] );

			return $transient;

		} catch ( \Throwable $e ) {
			RLog::error( 'Revolut_Update_Plugin_Action : ' . $e->getMessage() );
			return $transient;
		}
	}

	/**
	 * @return array{is_beta_eligible: bool, rollback: bool, version: string, download_url: string}|null
	 * @throws Exception Throws exception.
	 */
	public function fetch_meta(): ?array {
		$cached = get_transient( self::CACHE_KEY );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$response = $this->api_client->request(
			'GET',
			$this->api_url . '/' . $this->store_details->getStoreDomain()
		);

		if ( isset( $response['error'] ) || $this->api_client->getStatusCode( $response ) !== 200 ) {
			throw new Exception( 'Revolut_Update_Manager fetch_meta error: ' . wp_json_encode( $response ) );
		}

		$data     = $this->api_client->getBody( $response );
		$required = array( 'is_beta_eligible', 'version', 'download_url', 'rollback' );

		foreach ( $required as $key ) {
			if ( ! array_key_exists( $key, $data ) ) {
				throw new Exception( 'Revolut_Update_Manager fetch_meta missing key ' . $key );
			}
		}

		$data['is_beta_eligible'] = (bool) $data['is_beta_eligible'];
		$data['rollback']         = (bool) $data['rollback'];
		$data['version']          = $data['version'];
		$data['download_url']     = $data['download_url'];

		set_transient( self::CACHE_KEY, $data, self::CACHE_TTL_SECONDES );
		return $data;
	}

	/**
	 * @param string                                                                               $current
	 * @param array{is_beta_eligible: bool, version: string, download_url: string, rollback: bool} $meta
	 */
	public function should_rollback( string $current, array $meta ): bool {
		if ( ! $meta['rollback'] ) {
			return false;
		}

		if ( ! $this->is_valid_update_payload( $meta ) ) {
			return false;
		}

		return version_compare( $current, $meta['version'], '>' );
	}

	/**
	 * @param string                                                                               $current
	 * @param array{is_beta_eligible: bool, version: string, download_url: string, rollback: bool} $meta
	 * @param object|null                                                                          $wporg_update
	 */
	public function should_apply_beta( string $current, array $meta, $wporg_update ): bool {
		if ( ! $meta['is_beta_eligible'] ) {
			return false;
		}

		if ( ! $this->is_valid_update_payload( $meta ) ) {
			return false;
		}

		if ( version_compare( $current, $meta['version'], '>=' ) ) {
			return false;
		}

		$wporg_version = isset( $wporg_update->new_version ) ? $wporg_update->new_version : '0';
		return version_compare( $wporg_version, $meta['version'], '<' );
	}

	public function update_obj( string $version, string $url ): \stdClass {
		return (object) array(
			'slug'        => self::PLUGIN_SLUG,
			'plugin'      => self::PLUGIN_FILE,
			'new_version' => $version,
			'url'         => 'https://wordpress.org/plugins/revolut-gateway-for-woocommerce/',
			'package'     => $url,
		);
	}

	/**
	 * @param array{is_beta_eligible: bool, version: string, download_url: string, rollback: bool} $meta
	 */
	public function is_valid_update_payload( array $meta ): bool {
		return $this->valid_version( $meta['version'] )
			&& filter_var( $meta['download_url'], FILTER_VALIDATE_URL );
	}

	public function valid_version( string $v ): bool {
		return (bool) preg_match( '/^\d+\.\d+\.\d+/', $v );
	}
}
