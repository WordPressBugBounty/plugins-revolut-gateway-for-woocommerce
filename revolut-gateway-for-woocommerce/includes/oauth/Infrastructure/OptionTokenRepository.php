<?php

namespace Revolut\Plugin\Infrastructure\Wordpress;

use Revolut\Plugin\Core\Infrastructure\TokenRepositoryInterface;
use Revolut\Plugin\Core\Infrastructure\OptionRepositoryInterface;
use Revolut\Plugin\Core\Models\Token;

class OptionTokenRepository implements TokenRepositoryInterface
{
    private const OPTION_KEY = 'revolut_merchant_api_tokens';

    private $optionRepository;

    public function __construct( OptionRepositoryInterface $optionRepository)
    {
        $this->optionRepository = $optionRepository;
    }

    public function saveTokens( Token $token )
    {        
        $this->optionRepository->addOrUpdate(
            self::OPTION_KEY,
            array(
                'access_token'  => $token->accessToken,
                'refresh_token' => $token->refreshToken,
            )
        );
    }

    public function getTokens()
    {
        $data = $this->optionRepository->get(self::OPTION_KEY);
        if (empty($data['access_token']) || empty($data['refresh_token'])) {
            return null;
        }
        return new Token($data['access_token'], $data['refresh_token']);
    }
}
