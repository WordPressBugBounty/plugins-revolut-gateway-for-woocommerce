<?php

namespace Revolut\Plugin\Core\Flows\AuthConnect;

interface AuthConnectResourceContract
{
    public function handleTokenExchange();
    public function handleDisconnect();
}
