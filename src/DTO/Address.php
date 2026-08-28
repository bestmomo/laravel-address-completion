<?php

namespace AddressCompletion\DTO;

final readonly class Address
{
    public function __construct(
        public string $label,
        public string $street,
        public string $postcode,
        public string $city,
    ) {}
}