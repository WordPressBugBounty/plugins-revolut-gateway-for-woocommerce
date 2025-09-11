<?php

namespace Revolut\Plugin\Infrastructure\Api\MerchantDetails;

interface MerchantDetailsApiInterface
{
    public function getDetails(): array;
    public function getFeatures(): array;
    public function availablePaymentMethods(int $amount, string $currency): array;
    public function getPublicKey(): string;
}
