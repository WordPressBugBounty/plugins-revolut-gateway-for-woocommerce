<?php

namespace Revolut\Plugin\Services;

use Revolut\Plugin\Services\Config\Store\StoreDetailsInterface;

class OpenBankingAvailabilityChecker
{
    const OPEN_BANKING_CAPABILLITY_FLAG = 'ENABLE_OPEN_BANKING_FOR_EUR';
    private $storeConfig;

    public function __construct(StoreDetailsInterface $storeConfig)
    {
        $this->storeConfig = $storeConfig;
    }

    public function isAvailable()
    {

        if ($this->storeConfig->getStoreLegalCountryCode() === 'UK') {
            return true;
        }

        return in_array(self::OPEN_BANKING_CAPABILLITY_FLAG, $this->storeConfig->getStoreFeatures());
    }
}
