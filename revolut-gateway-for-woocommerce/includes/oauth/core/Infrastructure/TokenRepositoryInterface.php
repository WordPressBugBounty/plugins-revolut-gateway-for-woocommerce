<?php

namespace Revolut\Plugin\Core\Infrastructure;

use Revolut\Plugin\Core\Models\Token;

interface TokenRepositoryInterface
{
    public function saveTokens( Token $token);
    public function getTokens();
}
