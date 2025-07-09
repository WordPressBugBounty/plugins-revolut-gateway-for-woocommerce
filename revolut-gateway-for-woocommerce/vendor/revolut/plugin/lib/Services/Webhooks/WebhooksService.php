<?php

namespace Revolut\Plugin\Services\Webhooks;

use Revolut\Plugin\Infrastructure\Api\Webhooks\WebhooksApiInterface;
use Revolut\Plugin\Services\Config\Api\ConfigInterface;
use Revolut\Plugin\Services\Log\RLog;
use Revolut\Plugin\Services\Repositories\OptionRepositoryInterface;

class WebhooksService implements WebhooksInterface
{
    private $webhooksApi;
    private $repo;
    private $config;
    public function __construct(
        WebhooksApiInterface $webhooksApi,
        OptionRepositoryInterface $repo,
        ConfigInterface $config
    ) {
        $this->webhooksApi = $webhooksApi;
        $this->repo = $repo;
        $this->config = $config;
    }

    public function registerWebhook($url, $events = [])
    {
        try {
            if (empty($events)) {
                throw new \Error('WebhooksService, no event specified');
            }

            $result = $this->webhooksApi->register($url, $events);

            $this->repo->addOrUpdate($this->WebhookUrlOptionKey(), $url);
            $this->repo->addOrUpdate($this->WebhookSigningSecretOptionKey(), $result['signing_secret']);

            RLog::debug("WebhookService, successfully registered webhook $url");

            return true;
        } catch (\Throwable $e) {
            RLog::error("WebhooksService, unable to register webhook: $url - " . $e->getMessage());
        }

        return false;
    }

    public function deleteWebhook($id)
    {
        try {
            $this->webhooksApi->delete($id);
            $this->repo->delete($this->WebhookUrlOptionKey());
            $this->repo->delete($this->WebhookSigningSecretOptionKey());
            return true;
        } catch (\Throwable $e) {
            RLog::error("WebhooksService, unable to delete webhook $id - " . $e->getMessage());
        }
        return false;
    }

    public function WebhookUrlOptionKey()
    {
        $mode = $this->config->getMode();
        return $mode . "_revolut_webhook_domain";
    }

    public function WebhookSigningSecretOptionKey()
    {
        $mode = $this->config->getMode();
        return $mode . "_revolut_webhook_domain_signing_secret";
    }
}
