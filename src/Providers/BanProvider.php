<?php

namespace AddressCompletion\Providers;

use AddressCompletion\Contracts\AddressProvider;
use AddressCompletion\DTO\Address;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class BanProvider implements AddressProvider
{
    private const int CACHE_TTL = 300; // 5 minutes

    private readonly string $banUrl;
    private readonly string $defaultCountry;
    private readonly array $banCountries;
    private readonly int $defaultLimit;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->banUrl = (string) config('address-completion.providers.ban.url');
        $this->defaultCountry = (string) config('address-completion.default_country');
        $this->banCountries = (array) config('address-completion.providers.ban.countries');
        $this->defaultLimit = (int) config('address-completion.limit');
    }

    /**
     * {@inheritdoc}
     */
    public function supports(string $country): bool
    {
        return in_array(
            $country,
            $this->banCountries,
            true
        );
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

            return Cache::remember($cacheKey, self::CACHE_TTL, function () use ($value, $effectiveLimit) {
                return $this->searchBan($value, $effectiveLimit);
            });
        } catch (\Throwable $e) {
            report($e);

            return [];
        }
    }

    /**
     * Search for an address by value using the BAN API.
     *
     * @return Address[]
     *
     * @throws \Illuminate\Http\Client\ConnectionException
     * @throws \Illuminate\Http\Client\RequestException
     */
    private function searchBan(string $value, int $limit): array
    {
        $response = Http::acceptJson()
            ->timeout(3)
            ->retry(2, 150)
            ->get($this->banUrl, [
                'q' => $value,
                'index' => 'address',
                'limit' => $limit,
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
        $properties = $feature['properties'] ?? [];

        return new Address(
            label: $properties['label'] ?? '',
            street: $properties['name'] ?? $properties['label'] ?? '',
            postcode: $properties['postcode'] ?? '',
            city: $properties['city'] ?? ''
        );
    }

    /**
     * Build a cache key for the given value, country and limit.
     */
    private function buildCacheKey(string $value, string $country, int $limit): string
    {
        return sprintf(
            'address_completion:ban:%s:%d:%s',
            $country,
            $limit,
            md5(mb_strtolower($value))
        );
    }
}