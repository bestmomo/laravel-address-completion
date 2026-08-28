<?php

declare(strict_types=1);

namespace AddressCompletion\AddressCompletion;

use AddressCompletion\Contracts\AddressProvider;

class AddressCompletion
{
    public function autocomplete(string $query, ?string $country = null): array
    {
        $country = strtoupper($country ?: (string) config('address-completion.default_country'));

        foreach ($this->providers() as $provider) {
            if ($provider->supports($country)) {
                return $provider->autocomplete($query, $country);
            }
        }

        return [];
    }

    /**
     * @return AddressProvider[]
     */
    private function providers(): array
    {
        $configuredProviders = (array) config('address-completion.providers');
        $defaultProvider = (string) config('address-completion.default_provider');
        $providerNames = array_unique([
            ...array_keys($configuredProviders),
            $defaultProvider,
        ]);

        usort($providerNames, function (string $first, string $second) use ($defaultProvider): int {
            return ($first === $defaultProvider ? 1 : 0) <=> ($second === $defaultProvider ? 1 : 0);
        });

        return array_values(array_filter(array_map(
            function (string $name) use ($configuredProviders): ?AddressProvider {
                $class = $configuredProviders[$name]['class'] ?? null;

                return is_string($class) ? app($class) : null;
            },
            $providerNames
        )));
    }
}
