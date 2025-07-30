<?php

namespace Revolut\Wordpress\Infrastructure\Config;

use Revolut\Wordpress\Infrastructure\OptionRepository;

use Revolut\Plugin\Infrastructure\Config\Api\Environment;
use Revolut\Plugin\Infrastructure\Config\Api\Config;
use Revolut\Plugin\Infrastructure\Config\Api\ProdConfig;
use Revolut\Plugin\Infrastructure\Config\Api\DevConfig;
use Revolut\Plugin\Infrastructure\Config\Api\SandboxConfig;
use Revolut\Plugin\Services\Config\Api\ConfigProviderInterface;
use Revolut\Plugin\Services\Repositories\TokenRepositoryInterface;

class ApiConfigProvider implements ConfigProviderInterface
{
    private $repository;
    private $configOptions;
    private $tokenRepository;

    function __construct(OptionRepository $repository, TokenRepositoryInterface $tokenRepository)
    {
        $this->repository = $repository;
        $this->tokenRepository = $tokenRepository;
        $this->configOptions = $this->repository->get('woocommerce_revolut_settings');
    }

    public function getConfig(?string $mode = null): Config
    {        
        if(!$mode){
            $mode = isset($this->configOptions['mode']) ? $this->configOptions['mode'] : '';
        }

        $mode = strtolower($mode);

        switch($mode){
            case Environment::PROD:
                $config = new ProdConfig();
                $config->setSecretKey($this->getSecretKey($mode));
                $config->setPublicKey($this->getPublicKey($mode));
                return $config;
            case Environment::DEV:
                $config = new DevConfig();
                $config->setSecretKey($this->getSecretKey($mode));
                $config->setPublicKey($this->getPublicKey($mode));
                return $config;
            case Environment::SANDBOX:
                $config = new SandboxConfig();
                $config->setSecretKey($this->getSecretKey($mode));
                $config->setPublicKey($this->getPublicKey($mode));
                return $config;
            default:
                $config = new ProdConfig();
                $config->setSecretKey($this->getConfigValue($mode));
                $config->setPublicKey($this->getPublicKey($mode));
                return $config;
        }   
    }

    public function getSecretKey($mode)
    {
        if($mode == Environment::PROD){
            return $this->getConfigValue('api_key');
        }

        return $this->getConfigValue('api_key_' . $mode);
    }

    public function getPublicKey($mode)
    {
        return get_option("{$mode}_revolut_merchant_public_key");
    }

    public function getConfigValue($key, $default = ''): string
    {
        return isset($this->configOptions[$key]) ? $this->configOptions[$key] : $default;
    }

    public function getTokens() {
        return $this->tokenRepository->getTokens();
    }
}
