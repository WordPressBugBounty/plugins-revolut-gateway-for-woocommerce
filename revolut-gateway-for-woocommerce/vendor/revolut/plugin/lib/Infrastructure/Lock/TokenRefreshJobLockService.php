<?php

namespace Revolut\Plugin\Infrastructure\Lock;

use Revolut\Plugin\Services\Repositories\OptionRepositoryInterface;

class TokenRefreshJobLockService extends LockService
{
    private const TOKEN_REFRESH_JOB_LOCK_OPTION  = 'revolut_plugin_token_refresh_job_db_lock';
    private const TOKEN_REFRESH_JOB_LOCK_TIMEOUT  = 9 * 60; // 9 minutes

    public function __construct(OptionRepositoryInterface $optionRepository)
    {
        parent::__construct(
            $optionRepository,
            self::TOKEN_REFRESH_JOB_LOCK_OPTION,
            self::TOKEN_REFRESH_JOB_LOCK_TIMEOUT
        );
    }
}
