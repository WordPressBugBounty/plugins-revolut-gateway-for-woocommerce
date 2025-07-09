<?php

namespace Revolut\Wordpress\Infrastructure;

use Revolut\Plugin\Services\Log\LoggerInterface;

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
        error_log(self::LOG_CONTEXT['source'] . " [INFO] " . $message);
    }
    
    public function error(string $message, array $context = self::LOG_CONTEXT)
    {
        $this->logger->error($message, $context);
        error_log(self::LOG_CONTEXT['source'] . "[ERROR] " . $message);
    }
    
    public function debug(string $message, array $context = self::LOG_CONTEXT)
    {
        $this->logger->debug($message, $context);
        error_log(self::LOG_CONTEXT['source'] . "[DEBUG] " . $message);
    }
}
