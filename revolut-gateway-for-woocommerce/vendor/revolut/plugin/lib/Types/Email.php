<?php

namespace Revolut\Plugin\Types;

use Exception;
use Revolut\Plugin\Types\ValueObject;

class Email extends ValueObject
{
    private $email;

    public function __construct(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email: $email");
        }

        $this->email = strtolower($email);
    }

    public function getValue(): string
    {
        return $this->email;
    }

    public static function of(string $email): Email
    {
        return new Email($email);
    }
}
