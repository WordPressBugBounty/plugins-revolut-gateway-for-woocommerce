<?php

namespace Revolut\Plugin\Infrastructure\Api\Webhooks;

interface WebhooksApiInterface
{
    public function register(string $url, array $events);
    public function delete(string $id);
    public function retrieveById($id);
}
