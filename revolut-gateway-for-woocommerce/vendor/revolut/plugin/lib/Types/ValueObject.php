<?php

namespace Revolut\Plugin\Types;

abstract class ValueObject
{
    public function equals(ValueObject $other): bool
    {
        return $this === $other;
    }

    abstract public function getValue();
}
