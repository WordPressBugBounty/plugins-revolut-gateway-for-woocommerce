<?php

namespace Revolut\Wordpress\Infrastructure;

use Revolut\Plugin\Services\Http\HttpClientInterface;
use Revolut\Plugin\Services\Log\RLog;

class HttpClient implements HttpClientInterface
{
    public function request(string $method, string $url, array $options = []): array
    {
        $headers = $options['headers'] ?? [];
        $body = $options['body'] ?? null;
        $query = $options['query'] ?? [];


        if (!empty($query)) {
            $url = add_query_arg($query, $url);
        }

        $args = [
            'method'  => strtoupper($method),
            'headers' => self::withUserAgent($headers),
            'body'    => $body,
            'timeout' => 10,
        ];

        $response = wp_remote_request($url, $args);
        if (is_wp_error($response)) {
            return [
                'status' => 0,
                'body'   => $response->get_error_message(),
                'error'  => true,
            ];
        }

        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = wp_remote_retrieve_body($response);        

        return [
            'status' => $response_code,
            'body'   => $response_body
        ];
    }

    public static function withUserAgent(array $headers)
    {
        global $wp_version;
        global $woocommerce;
        $headers['User-Agent'] = 'Revolut Payment Gateway/' . WC_GATEWAY_REVOLUT_VERSION .
                                 ' WooCommerce/' . $woocommerce->version . 
                                 ' Wordpress/' . $wp_version . ' PHP/' . PHP_VERSION;
        return $headers;
    }

    public function getStatusCode(array $response): int
    {
        return $response['status'] ?? 0;
    }

    public function getBody(array $response): array
    {
        return json_decode($response['body'], true) ?? [];
    }

    public function isError(array $response): bool
    {
        return isset($response['code']);
    }

    public function getErrorMessage(array $response): string
    {
        return $response['message'] ?? "";
    }
}
