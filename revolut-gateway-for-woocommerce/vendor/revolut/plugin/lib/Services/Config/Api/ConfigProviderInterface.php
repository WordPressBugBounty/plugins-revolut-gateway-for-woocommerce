<?php

namespace Revolut\Plugin\Services\Config\Api;

use Revolut\Plugin\Services\Config\Api\ConfigInterface;

interface ConfigProviderInterface
{
    public function getConfig(?string $mode = null): ConfigInterface;
}
