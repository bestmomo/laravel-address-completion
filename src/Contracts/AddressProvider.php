<?php

namespace AddressCompletion\Contracts;

use AddressCompletion\DTO\Address;

interface AddressProvider
{
    /**
     * @return bool
     */
    public function supports(string $country): bool;

    /**
     * @return Address[]
     */
    public function autocomplete(string $query, ?string $country = null, ?int $limit = null): array;
}