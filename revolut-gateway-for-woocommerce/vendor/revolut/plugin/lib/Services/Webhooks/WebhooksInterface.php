<?php

namespace Revolut\Plugin\Services\Webhooks;

interface WebhooksInterface
{
    public function registerWebhook($url, array $events);
}
