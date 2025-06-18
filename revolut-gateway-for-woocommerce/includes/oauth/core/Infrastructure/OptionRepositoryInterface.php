<?php

namespace Revolut\Plugin\Core\Infrastructure;

interface OptionRepositoryInterface
{
    public function add(string $name, mixed $value);
    public function get(string $name);
    public function addOrUpdate(string $name, mixed $value);
    public function update(string $name, mixed $value);
    public function delete(string $name);
}
