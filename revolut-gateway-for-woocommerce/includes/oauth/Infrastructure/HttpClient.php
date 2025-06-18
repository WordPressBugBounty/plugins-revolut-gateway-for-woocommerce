<?php

namespace Revolut\Plugin\Infrastructure\Wordpress;

use Revolut\Plugin\Core\Infrastructure\HttpClientInterface;

class HttpClient implements HttpClientInterface
{
    public function post( $url, $params, $headers = array() )
    {
        $request['body'] = $params;
        if ($headers) {
            $request['headers'] = $headers;
        }
        $response = wp_remote_post($url, $request);
        if (is_wp_error($response)) {
            throw new \Exception('HTTP error');
        }
        return json_decode(wp_remote_retrieve_body($response), true);
    }
}
