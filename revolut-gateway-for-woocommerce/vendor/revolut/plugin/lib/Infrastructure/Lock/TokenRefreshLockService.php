<?php

namespace Revolut\Plugin\Infrastructure\Lock;

use Revolut\Plugin\Services\Repositories\OptionRepositoryInterface;

class TokenRefreshLockService extends LockService
{
    private const ON_DEMAND_TOKEN_REFRESH_LOCK_OPTION  = 'revolut_plugin_on_demand_token_refresh_db_lock';
    private const ON_DEMAND_TOKEN_REFRESH_LOCK_TIMEOUT  = 30; // 30 seconds

    public function __construct(OptionRepositoryInterface $optionRepository)
    {
        parent::__construct(
            $optionRepository,
            self::ON_DEMAND_TOKEN_REFRESH_LOCK_OPTION,
            self::ON_DEMAND_TOKEN_REFRESH_LOCK_TIMEOUT
        );
    }
}
