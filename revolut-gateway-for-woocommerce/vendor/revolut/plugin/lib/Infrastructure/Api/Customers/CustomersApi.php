<?php

namespace Revolut\Plugin\Infrastructure\Api\Customers;

use Revolut\Plugin\Infrastructure\Api\MerchantApiClientInterface;
use Revolut\Plugin\Infrastructure\Api\Customers\DTOs\CustomerDTO;
use Revolut\Plugin\Infrastructure\Api\Customers\DTOs\CustomerDTOFactory;
use Revolut\Plugin\Infrastructure\Api\Customers\DTOs\CustomerSavedPaymentMethodDTOFactory;
use Revolut\Plugin\Types\Email;
use Revolut\Plugin\Types\PhoneNumber;

class CustomersApi implements CustomersApiInterface
{
    private $merchantApiClient;

    public function __construct(MerchantApiClientInterface $merchantApiClient)
    {
        $this->merchantApiClient = $merchantApiClient;
    }

    public function create(Email $email, ?string $fullName, ?PhoneNumber $phoneNumber): CustomerDTO
    {

        $customer = $this->merchantApiClient->post('customers', [
            'email' => $email->getValue(),
            'full_name' => $fullName ? $fullName : null,
            'phone' => $phoneNumber ? $phoneNumber->getValue() : null
        ]);

        return CustomerDTOFactory::fromArray($customer);
    }

    public function getById(string $customerId): CustomerDTO
    {
        $customer = $this->merchantApiClient->get("customers/$customerId");
        return CustomerDTOFactory::fromArray($customer);
    }


    public function getSavedPaymentMethod(string $customerId): array
    {
        $savedPaymentMethods = $this->merchantApiClient->get("customer/$customerId/payment-methods");

        return array_map(function (array $item) {
            return CustomerSavedPaymentMethodDTOFactory::fromArray($item);
        }, $savedPaymentMethods);
    }
}
