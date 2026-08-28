<?php

declare(strict_types=1);

namespace AddressCompletion\Providers;

use AddressCompletion\Contracts\AddressProvider;
use AddressCompletion\DTO\Address;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class GeoapifyProvider implements AddressProvider
{
    private const int CACHE_TTL = 300; // 5 minutes

    private readonly string $geoapifyUrl;
    private readonly string $geoapifyKey;
    private readonly string $defaultCountry;
    private readonly int $defaultLimit;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->geoapifyUrl = (string) config('address-completion.providers.geoapify.url');
        $this->defaultCountry = (string) config('address-completion.default_country');
        $this->defaultLimit = (int) config('address-completion.limit');
        $this->geoapifyKey = (string) config('address-completion.providers.geoapify.key');
    }

    public function supports(string $country): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function autocomplete(string $query, ?string $country = null, ?int $limit = null): array
    {
        $value = trim($query);
        $country = strtoupper($country ?: $this->defaultCountry);
        $effectiveLimit = $limit ?? $this->defaultLimit;

        if (mb_strlen($value) < 5) {
            return [];
        }

        try {
            $cacheKey = $this->buildCacheKey($value, $country, $effectiveLimit);

            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($value, $country, $effectiveLimit) {
                return $this->searchGeoapify($value, $country, $effectiveLimit);
            });
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * Search for an address by value using the Geoapify API.
     *
     * @return Address[]
     *
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function searchGeoapify(string $value, string $country, int $limit): array
    {
        $response = Http::acceptJson()
            ->timeout(3)
            ->retry(2, 150)
            ->get($this->geoapifyUrl, [
                'text' => $value,
                'limit' => $limit,
                'filter' => 'countrycode:' . strtolower($country),
                'apiKey' => $this->geoapifyKey,
            ])->throw();

        return array_map(
            $this->mapFeature(...),
            $response->json('features', [])
        );
    }

    /**
     * Map a feature to an address.
     */
    private function mapFeature(array $feature): Address
    {
        $p = $feature['properties'] ?? [];

        return new Address(
            label: $p['formatted'] ?? '',
            street: trim(
                ($p['housenumber'] ?? '') . ' ' .
                ($p['street'] ?? '')
            ) ?: ($p['formatted'] ?? ''),
            postcode: $p['postcode'] ?? '',
            city: $p['city']
                ?? $p['town']
                ?? $p['village']
                ?? $p['municipality']
                ?? ''
        );
    }

    /**
     * Build a cache key for the given value, country and limit.
     */
    private function buildCacheKey(string $value, string $country, int $limit): string
    {
        return sprintf(
            'address_completion:geoapify:%s:%d:%s',
            $country,
            $limit,
            md5(mb_strtolower($value))
        );
    }
}