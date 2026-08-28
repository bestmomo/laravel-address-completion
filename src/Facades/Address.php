<?php

declare(strict_types=1);

namespace AddressCompletion\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AddressCompletion\AddressManager
 */
class Address extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \AddressCompletion\AddressManager::class;
    }
}
