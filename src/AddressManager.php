<?php

declare(strict_types=1);

namespace AddressCompletion;

use AddressCompletion\Contracts\AddressProvider;
use AddressCompletion\DTO\Address;

class AddressManager
{
    private readonly string $defaultCountry;

    /**
     * @param AddressProvider[] $providers
     */
    public function __construct(
        private readonly array $providers,
    ) {
        $this->defaultCountry = (string) config('address-completion.default_country');
    }

    /**
     * @return Address[]
     */
    public function search(string $query, ?string $country = null, ?int $limit = null): array
    {
        $country = strtoupper($country ?: $this->defaultCountry);

        foreach ($this->providers as $provider) {
            if ($provider->supports($country)) {
                return $provider->autocomplete($query, $country, $limit);
            }
        }

        return [];
    }
}