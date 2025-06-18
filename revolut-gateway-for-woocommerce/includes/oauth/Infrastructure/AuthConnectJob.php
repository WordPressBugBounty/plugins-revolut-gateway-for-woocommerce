<?php

namespace Revolut\Plugin\Infrastructure\Wordpress;

use Revolut\Plugin\Core\Services\RLog;
use Revolut\Plugin\Core\Exceptions\TokenRefreshInProgressException;
use Revolut\Plugin\Core\Flows\AuthConnect\AuthConnect;

class AuthConnectJob
{
    private $authConnectService;

    function __construct(AuthConnect $authConnectService)
    {
        $this->authConnectService = $authConnectService;
    }

    public function run()
    {
        add_action('init',  array($this, 'handleTokenRefreshJob'));
    }

    public function handleTokenRefreshJob()
    {
        $api_settings = revolut_wc()->api_settings;
        $mode = $api_settings->get_option('mode');
   
        try {
            $this->authConnectService->refreshTokenJob($mode);
        } catch (\Exception $e) {
            if($e instanceof TokenRefreshInProgressException){
                return;
            }

            RLog::error("refresh_token job error. " . $e->getMessage());
        }
    }
}
