<?php

namespace Revolut\Plugin\Infrastructure\Api;

interface MerchantApiClientInterface
{
    public function get(string $path, array $query = []): array;
    public function post(string $path, array $data = []): array;
    public function put(string $path, array $data = []): array;
    public function patch(string $path, array $data = []): array;
    public function delete(string $path, array $data = []): array;
}
