<?php

namespace Revolut\Plugin\Infrastructure\Api\Customers\DTOs;

use Revolut\Plugin\Infrastructure\Api\Customers\DTOs\CustomerSavedPaymentMethodDTO;

class CustomerSavedPaymentMethodDTOFactory
{
    public static function fromArray(array $savedPaymentMethod): CustomerSavedPaymentMethodDTO
    {
        $id = $savedPaymentMethod['id'];
        $type = $savedPaymentMethod['type'];
        $methodDetails = $savedPaymentMethod['method_details'];

        return new CustomerSavedPaymentMethodDTO($id, $type, $methodDetails);
    }
}
