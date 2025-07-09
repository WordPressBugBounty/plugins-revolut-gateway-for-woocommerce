<?php

namespace Revolut\Plugin\Services\ApplePay;

use Revolut\Plugin\Infrastructure\Api\ApplePay\ApplePayApiInterface;
use Revolut\Plugin\Infrastructure\FileSystem\FileSystemInterface;
use Revolut\Plugin\Services\Log\RLog;

class ApplePayOnboardingService implements ApplePayOnboardingInterface
{
    const ONBOARDING_FILE_REMOTE_DOWNLOAD_LINK = 'https://assets.revolut.com/api-docs/merchant-api/files/apple-developer-merchantid-domain-association';
    const ONBOARDING_FILE_NAME = 'apple-developer-merchantid-domain-association';

    private $fileSystem;
    private $onboardingFilePath;
    private $onboardingFileDir;
    private $applePayApi;
    public function __construct(FileSystemInterface $fileSystemAdapter, ApplePayApiInterface $applePayApi)
    {
        $this->fileSystem = $fileSystemAdapter;
        $this->onboardingFileDir = rtrim($this->fileSystem->getRootDir(), '/') . '/.well-known/';
        $this->onboardingFilePath = $this->onboardingFileDir . self::ONBOARDING_FILE_NAME;
        $this->applePayApi = $applePayApi;
    }

    public function downloadOnboardingCertificate()
    {
        if (!$this->fileSystem->fileExists($this->onboardingFileDir) && ! $this->fileSystem->makeDirectory($this->onboardingFileDir)) {
            RLog::error("ApplePayOnboardingService, Unable to create .well-known folder");
            return false;
        }

        $onboardingCertificateContent = $this->fileSystem->readFile(self::ONBOARDING_FILE_REMOTE_DOWNLOAD_LINK);

        if (! $this->fileSystem->writeFile($this->onboardingFilePath, $onboardingCertificateContent)) {
            RLog::error("ApplePayOnboardingService, Unable to write onboarding file : " . $this->onboardingFilePath);
            return false;
        }

        return true;
    }

    public function removeOnboardingCertificate()
    {
        if (! $this->fileSystem->fileExists($this->onboardingFilePath)) {
            RLog::debug("ApplePayOnboardingService, unable to remove onboarding file because it does not exist");
            return false;
        }

        if (! $this->fileSystem->deleteFile($this->onboardingFilePath)) {
            RLog::debug("ApplePayOnboardingService, unable to remove onboarding file");
            return false;
        }

        return true;
    }

    public function onBoardDomain($domain)
    {
        try {
            if ($this->downloadOnboardingCertificate()) {
                $this->applePayApi->registerDomain($domain);
                RLog::debug("ApplePayOnboardingService successfully registered $domain");
                $this->removeOnboardingCertificate();
                return true;
            }
        } catch (\Throwable $e) {
            RLog::error("ApplePayOnboardingService Error: " . $e->getMessage());
        }

        return false;
    }
}
