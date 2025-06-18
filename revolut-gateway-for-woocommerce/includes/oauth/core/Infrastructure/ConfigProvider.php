<?php

namespace Revolut\Plugin\Core\Infrastructure;

class ConfigProvider
{
    public const DEV = "dev";
    public const PROD = "live";

    private const DEV_AUTH_TOKEN_ENDPOINT = 'https://merchant.revolut.codes/api/oauth';
    private const LIVE_AUTH_TOKEN_ENDPOINT = 'https://merchant.revolut.com/api/oauth';

    private const DEV_AUTH_APP_CLIENT_ID = '8440f36e-7849-40cb-8cc7-4ed4e16553a9';
    private const LIVE_AUTH_APP_CLIENT_ID = '9cda975e-016c-4b49-b5c6-37d1285ba046';


    public function getAuthTokenEndpoint( $env)
    {
        return $env == self::DEV ? self::DEV_AUTH_TOKEN_ENDPOINT : self::LIVE_AUTH_TOKEN_ENDPOINT;
    }

    public function getAuthAppClientId( $env)
    {
        return $env == self::DEV ? self::DEV_AUTH_APP_CLIENT_ID : self::LIVE_AUTH_APP_CLIENT_ID;
    }
}
