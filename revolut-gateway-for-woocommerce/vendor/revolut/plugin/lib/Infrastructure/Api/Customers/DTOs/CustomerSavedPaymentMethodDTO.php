<?php

namespace Revolut\Plugin\Infrastructure\Api\Customers\DTOs;

class CustomerSavedPaymentMethodDTO
{
    public $id;
    public $type;
    public $methodDetails;

    public function __construct(string $id, string $type, array $methodDetails)
    {
        $this->id = $id;
        $this->type = $type;
        $this->methodDetails = $methodDetails;
    }
}
