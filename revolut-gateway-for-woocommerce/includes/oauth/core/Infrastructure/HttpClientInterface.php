<?php

namespace Revolut\Plugin\Core\Infrastructure;

interface HttpClientInterface
{
    public function post( $url, $params);
}
