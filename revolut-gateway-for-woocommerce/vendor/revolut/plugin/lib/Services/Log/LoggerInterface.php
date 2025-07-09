<?php

namespace Revolut\Plugin\Services\Log;

interface LoggerInterface
{
    public function info(string $message, array $context);
    public function error(string $message, array $context);
    public function debug(string $message, array $context);
}
