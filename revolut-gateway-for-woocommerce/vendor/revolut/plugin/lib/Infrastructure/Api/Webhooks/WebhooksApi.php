<?php

namespace Revolut\Plugin\Infrastructure\Api\Webhooks;

use Error;
use Revolut\Plugin\Infrastructure\Api\Webhooks\WebhooksApiInterface;
use Revolut\Plugin\Infrastructure\Api\MerchantApiClientInterface;

class WebhooksApi implements WebhooksApiInterface
{
    private $merchantApiClient;

    public function __construct(MerchantApiClientInterface $merchantApiClient)
    {
        $this->merchantApiClient = $merchantApiClient;
    }

    public function register(string $url, array $events)
    {
        $data = array(
            "url" => $url,
            "events" => $events
        );

        $response = $this->merchantApiClient->post('webhooks', $data);

        if (isset($response['id']) && ! empty($response['id']) && ! empty($response['signing_secret'])) {
            return $response;
        }

        throw new Error("Unable to register webhook");
    }

    public function delete($id)
    {
        return $this->merchantApiClient->delete("webhooks/$id");
    }

    public function retrieveById($id)
    {
        throw new Error("Not implemented");
    }
}
