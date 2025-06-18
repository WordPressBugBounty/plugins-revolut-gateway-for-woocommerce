<?php

namespace Revolut\Plugin\Core\Infrastructure;

interface LoggerInterface
{
    public function info(string $message, mixed $context);
    public function error(string $message, mixed $context);
    public function debug(string $message, mixed $context);
}
