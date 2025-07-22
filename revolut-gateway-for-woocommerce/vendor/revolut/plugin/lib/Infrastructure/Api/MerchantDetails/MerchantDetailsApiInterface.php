<?php

namespace Revolut\Plugin\Infrastructure\Api\MerchantDetails;

interface MerchantDetailsApiInterface
{
    public function getDetails(): array;
    public function getFeatures(): array;
    public function hasFeature(string $feature): bool;
    public function availablePaymentMethods(int $amount, string $currency): array;
}
