<?php

namespace Revolut\Plugin\Infrastructure\Lock;

use Revolut\Plugin\Services\Lock\LockInterface;
use Revolut\Plugin\Services\Repositories\OptionRepositoryInterface;

class LockService implements LockInterface
{
    private $optionRepository;

    private $lockValue = null;

    private $timeoutSeconds;

    private $lockName;

    public function __construct(
        OptionRepositoryInterface $optionRepository,
        string $lockName,
        int $timeoutSeconds = 20
    ) {
        $this->optionRepository = $optionRepository;
        $this->timeoutSeconds = $timeoutSeconds;
        $this->lockName = $lockName;
    }

    public function acquire()
    {
        $now     = time();
        $expires = $now + $this->timeoutSeconds;

        $value   = bin2hex(random_bytes(16)) . ':' . $expires;

        $current = $this->optionRepository->get($this->lockName);

        if ($current) {
            list($curVal, $curExpire) = explode(':', $current);
            if ($now < intval($curExpire)) {
                return false;
            }
        }

        $added = $this->optionRepository->add($this->lockName, $value);
        if (! $added) {
            $current                  = $this->optionRepository->get($this->lockName);
            list($curVal, $curExpire) = explode(':', $current);
            if ($now >= intval($curExpire)) {
                $this->optionRepository->update($this->lockName, $value);
                $current = $this->optionRepository->get($this->lockName);
                if ($current === $value) {
                    $this->lockValue = $value;
                    return true;
                }
            }
            return false;
        }

        $this->lockValue = $value;
        return true;
    }

    public function release()
    {
        if ($this->lockValue && $this->optionRepository->get($this->lockName) === $this->lockValue) {
            $this->optionRepository->delete($this->lockName);
            $this->lockValue = null;
        }
    }

    public function isLocked()
    {
        $current = $this->optionRepository->get($this->lockName);
        $now     = time();
        if ($current) {
            list($curVal, $curExpire) = explode(':', $current);
            if ($now < intval($curExpire)) {
                return true;
            }
        }

        return false;
    }
}
