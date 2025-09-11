<?php

namespace Revolut\Plugin\Services\Config\Merchant;

interface MerchantDetailsServiceInterface
{
    public function getAvailablePaymentMethods(?int $amount = null, ?string $currency = null): array;
    public function setupMerchantPublicKey(): void;
    public function getMerchantFeatures(): array;
    public function hasFeature(string $feature): bool;
}
