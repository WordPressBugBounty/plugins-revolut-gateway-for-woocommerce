<?php

namespace Revolut\Plugin\Core\Models;

class Token
{
    public $accessToken;
    public $refreshToken;

    public function __construct( $accessToken, $refreshToken )
    {
        $this->accessToken  = $accessToken;
        $this->refreshToken = $refreshToken;
    }
}
