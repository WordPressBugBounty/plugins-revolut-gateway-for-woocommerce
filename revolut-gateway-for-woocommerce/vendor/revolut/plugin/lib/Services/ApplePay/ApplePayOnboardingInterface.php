<?php

namespace Revolut\Plugin\Services\ApplePay;

interface ApplePayOnboardingInterface
{
    public function downloadOnboardingCertificate();
    public function removeOnboardingCertificate();
    public function onBoardDomain($domain);
}
