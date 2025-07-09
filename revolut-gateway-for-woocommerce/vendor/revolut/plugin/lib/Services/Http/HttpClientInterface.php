<?php

namespace Revolut\Plugin\Services\Http;

interface HttpClientInterface
{
    public function request(string $method, string $url, array $options = []): array;
    public function getStatusCode(array $response): int;
    public function getBody(array $response): array;
    public function isError(array $response): bool;
    public function getErrorMessage(array $response): ?string;
}
