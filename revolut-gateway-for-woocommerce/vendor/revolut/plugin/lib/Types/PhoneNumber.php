<?php

namespace Revolut\Plugin\Types;

use Revolut\Plugin\Types\ValueObject;

class PhoneNumber extends ValueObject
{
    private $phoneNumber;

    public function __construct(string $phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    public function getValue(): string
    {
        return $this->phoneNumber;
    }

    public static function of(string $phoneNumber): PhoneNumber
    {
        return new PhoneNumber($phoneNumber);
    }
}
