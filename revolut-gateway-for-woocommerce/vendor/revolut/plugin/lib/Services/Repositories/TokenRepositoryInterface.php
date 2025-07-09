<?php

namespace Revolut\Plugin\Services\Repositories;

use Revolut\Plugin\Domain\Model\Token;

interface TokenRepositoryInterface
{
    public function saveTokens(Token $token);
    public function getTokens();
}
