<?php

namespace Revolut\Plugin\Core\Infrastructure;

interface LoggerInterface
{
    public function info(string $message, array $context);
    public function error(string $message, array $context);
    public function debug(string $message, array $context);
}
