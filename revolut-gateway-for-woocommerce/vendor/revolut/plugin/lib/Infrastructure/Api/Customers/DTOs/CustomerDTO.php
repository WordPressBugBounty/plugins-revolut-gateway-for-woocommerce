<?php

namespace Revolut\Plugin\Infrastructure\Api\Customers\DTOs;

use Revolut\Plugin\Types\Email;
use Revolut\Plugin\Types\PhoneNumber;

class CustomerDTO
{
    public $id;
    public $fullName;
    public $email;
    public $phone;

    public function __construct(string $id, Email $email, ?PhoneNumber $phone = null, ?string $fullName = null)
    {
        $this->id = $id;
        $this->fullName = $fullName;
        $this->email = $email;
        $this->phone = $phone;
    }
}
