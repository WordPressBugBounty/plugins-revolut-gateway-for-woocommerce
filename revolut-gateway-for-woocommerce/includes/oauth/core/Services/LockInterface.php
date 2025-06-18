<?php

namespace Revolut\Plugin\Core\Interfaces\Services;

interface LockInterface
{
    public function acquire();
    public function release();
    public function isLocked();
}
