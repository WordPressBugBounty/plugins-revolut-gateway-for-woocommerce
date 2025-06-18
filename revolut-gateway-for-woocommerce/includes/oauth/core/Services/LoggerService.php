<?php

namespace Revolut\Plugin\Core\Services;

use Revolut\Plugin\Core\Infrastructure\LoggerInterface;

class RLog
{
    private static $logger;
    private static $context;

    public static function setLogger(LoggerInterface $logger, array $context)
    {
        self::$logger = $logger;
        self::$context = $context;
    }

    public static function info($message)
    {
        self::$logger->info($message, self::$context);
    }

    public static function error($message)
    {
        self::$logger->error($message, self::$context);
    }

    public static function debug($message)
    {
        self::$logger->debug($message, self::$context);
    }
}
