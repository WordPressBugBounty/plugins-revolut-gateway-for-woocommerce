<?php

namespace Revolut\Plugin\Infrastructure\Api\Customers;

use Revolut\Plugin\Infrastructure\Api\Customers\DTOs\CustomerDTO;
use Revolut\Plugin\Infrastructure\Api\Customers\DTOs\SavedPaymentMethodDTO;
use Revolut\Plugin\Types\Email;
use Revolut\Plugin\Types\PhoneNumber;

interface CustomersApiInterface
{
    public function create(Email $email, ?string $fullName, ?PhoneNumber $phoneNumber): CustomerDTO;
    public function getById(string $customerId): CustomerDTO;

    /**
     * @return SavedPaymentMethodDTO[]
     */
    public function getSavedPaymentMethod(string $customerId): array;
}
