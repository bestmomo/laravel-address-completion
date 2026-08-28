<?php

declare(strict_types=1);

use AddressCompletion\DTO\Address;
use AddressCompletion\Providers\BanProvider;
use AddressCompletion\Providers\GeoapifyProvider;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

it('maps a BAN response to addresses with custom limit', function () {
    Cache::flush();

    Http::fake([
        'https://data.geopf.fr/geocodage/search*' => Http::response([
            'features' => [
                [
                    'properties' => [
                        'label' => '10 Rue de Paris',
                        'name' => '10 Rue de Paris',
                        'postcode' => '75001',
                        'city' => 'Paris',
                    ],
                ],
            ],
        ]),
    ]);

    $result = (new BanProvider())->autocomplete('10 rue', 'FR', limit: 3);

    expect($result)->toEqual([
        new Address(
            label: '10 Rue de Paris',
            street: '10 Rue de Paris',
            postcode: '75001',
            city: 'Paris',
        ),
    ]);

    Http::assertSent(function (Request $request): bool {
        return str_starts_with($request->url(), 'https://data.geopf.fr/geocodage/search')
            && $request['q'] === '10 rue'
            && $request['index'] === 'address'
            && $request['limit'] === 3;
    });
});

it('maps a Geoapify response to addresses with custom limit', function () {
    Cache::flush();
    config(['address-completion.providers.geoapify.key' => 'test-key']);

    Http::fake([
        'https://api.geoapify.com/v1/geocode/autocomplete*' => Http::response([
            'features' => [
                [
                    'properties' => [
                        'formatted' => '10 Rue de Paris, 75001 Paris',
                        'housenumber' => '10',
                        'street' => 'Rue de Paris',
                        'postcode' => '75001',
                        'city' => 'Paris',
                    ],
                ],
            ],
        ]),
    ]);

    $result = (new GeoapifyProvider())->autocomplete('10 rue', 'fr', limit: 8);

    expect($result)->toEqual([
        new Address(
            label: '10 Rue de Paris, 75001 Paris',
            street: '10 Rue de Paris',
            postcode: '75001',
            city: 'Paris',
        ),
    ]);

    Http::assertSent(function (Request $request): bool {
        return str_starts_with($request->url(), 'https://api.geoapify.com/v1/geocode/autocomplete')
            && $request['text'] === '10 rue'
            && $request['limit'] === 8
            && $request['filter'] === 'countrycode:fr'
            && $request['apiKey'] === 'test-key';
    });
});

it('uses fallback default limit when no limit parameter is passed', function () {
    Cache::flush();
    config(['address-completion.limit' => 10]);

    Http::fake([
        'https://data.geopf.fr/geocodage/search*' => Http::response(['features' => []]),
    ]);

    (new BanProvider())->autocomplete('10 rue', 'FR');

    Http::assertSent(fn (Request $request) => $request['limit'] === 10);
});