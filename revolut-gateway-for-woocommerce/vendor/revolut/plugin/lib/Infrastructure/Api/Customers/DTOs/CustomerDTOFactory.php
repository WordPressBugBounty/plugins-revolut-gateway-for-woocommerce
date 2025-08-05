<?php

namespace Revolut\Plugin\Infrastructure\Api\Customers\DTOs;

use Revolut\Plugin\Infrastructure\Api\Customers\DTOs\CustomerDTO;
use Revolut\Plugin\Types\Email;
use Revolut\Plugin\Types\PhoneNumber;

class CustomerDTOFactory
{
    public static function fromArray($customer): CustomerDTO
    {
        $id = $customer['id'];
        $email = Email::of($customer['email']);
        $fullName = isset($customer['full_name']) ? $customer['full_name'] : null;
        $phoneNumber = isset($customer['phone']) ? PhoneNumber::of($customer['phone']) : null;

        return new CustomerDTO($id, $email, $phoneNumber, $fullName);
    }
}
