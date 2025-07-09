<?php

namespace Revolut\Plugin\Services\AuthConnect;

interface AuthConnectResourceContract
{
    public function handleTokenExchange();
    public function handleDisconnect();
}
