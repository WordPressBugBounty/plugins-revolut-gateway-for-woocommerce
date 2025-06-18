<?php

namespace Revolut\Plugin\Infrastructure\Wordpress;

use Revolut\Plugin\Core\Infrastructure\LoggerInterface;

class Logger implements LoggerInterface
{
    private const LOG_CONTEXT = array( 'source' => 'revolut-gateway-for-woocommerce' );
    
    private $logger;

    function __construct()
    {
        $this->logger = wc_get_logger();
    }   

    public function info(string $message, array $context = self::LOG_CONTEXT)
    {
        $this->logger->info($message, $context);
    }
    
    public function error(string $message, array $context = self::LOG_CONTEXT)
    {
        $this->logger->error($message, $context);
    }
    
    public function debug(string $message, array $context = self::LOG_CONTEXT)
    {
        $this->logger->debug($message, $context);
    }
}
