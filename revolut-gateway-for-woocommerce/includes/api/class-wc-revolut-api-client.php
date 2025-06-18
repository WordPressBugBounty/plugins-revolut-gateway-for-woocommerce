<?php
/**
 * Revolut API Client
 *
 * @package WooCommerce
 * @since   2.0
 * @author  Revolut
 */

defined( 'ABSPATH' ) || exit();

use Revolut\Plugin\Infrastructure\Wordpress\OptionRepository;
use Revolut\Plugin\Infrastructure\Wordpress\OptionTokenRepository;
use Revolut\Plugin\Core\Exceptions\TokenRefreshInProgressException;
use Revolut\Plugin\Core\Flows\AuthConnect\AuthConnect;
use Revolut\Plugin\Infrastructure\Wordpress\HttpClient;
use Revolut\Plugin\Core\Services\TokenRefreshLockService;
use Revolut\Plugin\Core\Services\TokenRefreshJobLockService;
use Revolut\Plugin\Core\Infrastructure\ConfigProvider;
use Revolut\Plugin\Core\Interfaces\Services\LockInterface;

/**
 * WC_Revolut_API_Client class.
 */
class WC_Revolut_API_Client {


	use WC_Revolut_Logger_Trait;

	/**
	 * Revolut Api Version
	 *
	 * @var string
	 */
	public $api_version = '2024-09-01';

	/**
	 * Api url live mode
	 *
	 * @var string
	 */
	public $api_url_live = 'https://merchant.revolut.com';

	/**
	 * Api url sandbox mode
	 *
	 * @var string
	 */
	public $api_url_sandbox = 'https://sandbox-merchant.revolut.com';

	/**
	 * Api url dev mode
	 *
	 * @var string
	 */
	public $api_url_dev = 'https://merchant.revolut.codes';

	/**
	 * Api mode live|sandbox|develop
	 *
	 * @var string
	 */
	public $mode;

	/**
	 * Api key
	 *
	 * @var string
	 */
	public $api_key;

	/**
	 * Refresh Token
	 *
	 * @var string
	 */
	public $access_token;

	/**
	 * Refresh Token
	 *
	 * @var string
	 */
	public $refresh_token;

	/**
	 * Public key
	 *
	 * @var string
	 */
	public $public_key;

	/**
	 * Api base url
	 *
	 * @var string
	 */
	public $base_url;

	/**
	 * Api url
	 *
	 * @var string
	 */
	public $api_url;

	/**
	 * API settings
	 *
	 * @var WC_Revolut_Settings_API
	 */
	private $api_settings;

	/**
	 * Token Service
	 *
	 * @var AuthConnect
	 */
	private $auth_connect;

	/**
	 * Token Repository
	 *
	 * @var OptionTokenRepository
	 */
	private $token_repo;

	/**
	 * Token Repository
	 *
	 * @var LockInterface
	 */
	private $on_demand_token_refresh_lock;

	/**
	 * Constructor
	 *
	 * @param WC_Revolut_Settings_API $api_settings Api settings.
	 * @param bool                    $new_api      api version.
	 * @param bool                    $force_tokens for using access token.
	 */
	public function __construct( WC_Revolut_Settings_API $api_settings, $new_api = false, $force_tokens = false ) {
		$this->api_settings = $api_settings;
		$this->mode         = $this->api_settings->get_option( 'mode' );
		$this->token_repo   = new OptionTokenRepository(
			new OptionRepository()
		);

		$this->on_demand_token_refresh_lock = new TokenRefreshLockService( new OptionRepository() );

		$this->auth_connect = new AuthConnect(
			$this->token_repo,
			new HttpClient(),
			new ConfigProvider(),
			$this->on_demand_token_refresh_lock
		);

		$api_tokens = $this->token_repo->getTokens();

		if ( 'live' === $this->mode ) {
			$this->base_url = $this->api_url_live;
			$this->api_key  = ! $force_tokens ? $this->api_settings->get_option( 'api_key' ) : '';
			// phpcs:ignore
			$this->refresh_token = $api_tokens ? $this->refresh_token = $api_tokens->refreshToken : null;
			// phpcs:ignore
			$this->access_token  = $api_tokens ? $this->access_token = $api_tokens->accessToken : null;
		} elseif ( 'sandbox' === $this->mode ) {
			$this->base_url = $this->api_url_sandbox;
			$this->api_key  = $this->api_settings->get_option( 'api_key_sandbox' );
		} elseif ( 'dev' === $this->mode ) {
			$this->base_url = $this->api_url_dev;
			$this->api_key  = ! $force_tokens ? $this->api_settings->get_option( 'api_key_dev' ) : '';
			// phpcs:ignore
			$this->refresh_token = $api_tokens ? $this->refresh_token = $api_tokens->refreshToken : null;
			// phpcs:ignore
			$this->access_token  = $api_tokens ? $this->access_token = $api_tokens->accessToken : null;
		}

		// switch to the new api if required.
		$this->api_url = $new_api ? $this->base_url . '/api' : $this->base_url . '/api/1.0';
	}

	/**
	 * Send post to API.
	 *
	 * @param string     $path Api path.
	 * @param array|null $body Request body.
	 * @param bool       $public Public API indicator.
	 * @param bool       $new_api New API indicator.
	 *
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function post( $path, $body = null, $public = false, $new_api = false ) {
		return $this->request( $path, 'POST', $body, $public, $new_api );
	}

	/**
	 * Send request to API
	 *
	 * @param string     $path             Api path.
	 * @param string     $method           Request method.
	 * @param array|null $body             Request body.
	 * @param bool       $public Public API indicator.
	 * @param bool       $new_api New API indicator.
	 * @return mixed
	 * @throws Exception Exception.
	 */
	private function request( $path, $method, $body = null, $public = false, $new_api = false ) {
		global $wp_version;
		global $woocommerce;

		if ( empty( $this->api_key ) && empty( $this->access_token ) ) {
			return array();
		}

		$api_key = $this->api_key;

		if ( empty( $api_key ) && ! empty( $this->access_token ) ) {
			$api_key = $this->access_token;
		}

		$url = $this->api_url . $path;

		if ( $new_api ) {
			$url = $this->base_url . '/api' . $path;
		}

		if ( $public ) {
			$api_key = $this->public_key;
			$url     = $this->base_url . '/api/public' . $path;
		}

		$request = array(
			'headers' => array(
				'Revolut-Api-Version' => $this->api_version,
				'Authorization'       => 'Bearer ' . $api_key,
				'User-Agent'          => 'Revolut Payment Gateway/' . WC_GATEWAY_REVOLUT_VERSION . ' WooCommerce/' . $woocommerce->version . ' Wordpress/' . $wp_version . ' PHP/' . PHP_VERSION,
				'Content-Type'        => 'application/json',
			),
			'method'  => $method,
		);

		if ( null !== $body ) {
			$request['body'] = wp_json_encode( $body );
		}

		$response      = wp_remote_request( $url, $request );
		$response_body = wp_remote_retrieve_body( $response );
		$response_code = wp_remote_retrieve_response_code( $response );

		if ( 401 === $response_code && ! $public && ! empty( $this->refresh_token ) ) {
			$this->try_refresh_token();

			if ( ! empty( $this->access_token ) ) {
				// retry request.
				$request['headers']['Authorization'] = 'Bearer ' . $this->access_token;
				$response                            = wp_remote_request( $url, $request );
				$response_body                       = wp_remote_retrieve_body( $response );
				$response_code                       = wp_remote_retrieve_response_code( $response );
			}
		}

		if ( $response_code >= 400 && $response_code < 500 && 'GET' !== $method ) {
			$this->log_error( "Failed request to URL $method $url" );
			$this->log_error( $response_body );
			throw new Exception( "Something went wrong: $method $url\n" . $response_body );
		}

		return json_decode( $response_body, true );
	}

	/**
	 * Try refresh access token
	 *
	 * @throws Exception Exception.
	 */
	private function try_refresh_token() {
		$this->log_error( 'try token refresh...' );
		$api_tokens = $this->token_repo->getTokens();

		// phpcs:ignore
		if ( $this->access_token !== $api_tokens->accessToken ) {
			$this->log_error( 'already refreshed' );
			// phpcs:ignore
			$this->refresh_token = $api_tokens->refreshToken;
			// phpcs:ignore
			$this->access_token  = $api_tokens->accessToken;
			return;
		}

		try {
			$this->log_error( 'try token refresh with - ' . $this->access_token );
			$this->auth_connect->refreshToken( $this->mode );
		} catch ( TokenRefreshInProgressException $e ) {
			$this->log_error( 'token refresh in progress with - ' . $this->access_token );
			$wait_up_to_sec = 0;
			$api_tokens     = $this->token_repo->getTokens();
			while ( $this->on_demand_token_refresh_lock->isLocked() && $wait_up_to_sec < 5 ) {
				$api_tokens = $this->token_repo->getTokens();
				$wait_up_to_sec++;
				sleep( 1 );
			}

			$this->log_error( 'awaited ' . ( $wait_up_to_sec ) . 's long' );

			$api_tokens = $this->token_repo->getTokens();

			// phpcs:ignore
			if ( $this->on_demand_token_refresh_lock->isLocked() && $this->access_token === $api_tokens->accessToken ) {
				throw new Exception( 'Token refreshing process took more than expected' );
			}
		}

		$api_tokens = $this->token_repo->getTokens();

		if ( empty( $api_tokens ) ) {
			throw new Exception( 'Can not load refreshed tokens' );
		}

		// phpcs:ignore
		$this->refresh_token = $api_tokens->refreshToken;
		// phpcs:ignore
		$this->access_token  = $api_tokens->accessToken;
	}

	/**
	 * Send GET request to API
	 *
	 * @param string $path Request path.
	 * @param bool   $public Public API indicator.
	 * @param bool   $new_api API version indicator.
	 *
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function get( $path, $public = false, $new_api = false ) {
		return $this->request( $path, 'GET', null, $public, $new_api );
	}

	/**
	 * Revolut API patch
	 *
	 * @param string     $path Request path.
	 * @param array|null $body Request body.
	 * @param bool       $public Public API indicator.
	 * @param bool       $new_api API version indicator.
	 *
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function patch( $path, $body, $public = false, $new_api = false ) {
		return $this->request( $path, 'PATCH', $body, $public, $new_api );
	}

	/**
	 * Revolut API delete
	 *
	 * @param string $path Request path.
	 *
	 * @return mixed
	 * @throws Exception Exception.
	 */
	public function delete( $path ) {
		return $this->request( $path, 'DELETE' );
	}

	/**
	 * Set Revolut Merchant Public Key
	 *
	 * @param string $public_key public key.
	 *
	 * @return void
	 */
	public function set_public_key( $public_key ) {
		$this->public_key = $public_key;
	}

	/**
	 * Returns API mode.
	 *
	 * @return string
	 */
	public function get_mode() {
		return $this->mode;
	}

	/**
	 * Checks API Developer mode.
	 *
	 * @return string
	 */
	public function is_dev_mode() {
		return 'dev' === $this->mode;
	}
}
