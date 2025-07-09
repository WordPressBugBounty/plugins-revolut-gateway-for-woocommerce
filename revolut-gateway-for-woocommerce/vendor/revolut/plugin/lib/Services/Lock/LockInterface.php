<?php

namespace Revolut\Plugin\Services\Lock;

interface LockInterface
{
    public function acquire();
    public function release();
    public function isLocked();
}
