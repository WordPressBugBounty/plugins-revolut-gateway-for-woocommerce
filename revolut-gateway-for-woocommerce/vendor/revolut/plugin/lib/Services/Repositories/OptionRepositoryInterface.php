<?php

namespace Revolut\Plugin\Services\Repositories;

interface OptionRepositoryInterface
{
    public function add($name, $value);
    public function get($name);
    public function addOrUpdate($name, $value);
    public function update($name, $value);
    public function delete($name);
}
