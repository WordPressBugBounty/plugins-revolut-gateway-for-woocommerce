<?php

namespace Revolut\Plugin\Presentation;

use Revolut\Plugin\Core\Infrastructure\HttpResourceInterface;
use Revolut\Plugin\Core\Flows\AuthConnect\AuthConnectResourceContract;
use Revolut\Plugin\Core\Flows\AuthConnect\AuthConnect;

class AuthConnectResource implements AuthConnectResourceContract, HttpResourceInterface
{
    private $authConnectService;

    function __construct(AuthConnect $authConnectService)
    {
        $this->authConnectService = $authConnectService;
    }

    public function registerRoutes()
    {
        add_action('wp_ajax_revolut_save_tokens', array($this, 'handleTokenExchange'));
        add_action('wp_ajax_revolut_remove_connection', array($this, 'handleDisconnect'));
    }

    public function handleTokenExchange()
    {
        check_ajax_referer('revolut_connect_nonce');
        
        $code     = sanitize_text_field($_POST['code'] ?? '');
        $verifier = sanitize_text_field($_POST['verifier'] ?? '');
        $mode     = sanitize_text_field($_POST['mode'] ?? '');

        try {
            $token = $this->authConnectService->exchangeAuthorizationCode($mode, $code, $verifier);
            wp_send_json_success(array( 'access_token' => $token->accessToken ));
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }

    public function handleDisconnect()
    {
        check_ajax_referer('revolut_disconnect_nonce');
    
        $mode     = sanitize_text_field($_POST['mode'] ?? '');

        try {
            $this->authConnectService->disconnect($mode);
            wp_send_json_success();
        } catch (\Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
}
