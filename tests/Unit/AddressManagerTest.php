<?php

declare(strict_types=1);

use AddressCompletion\AddressManager;
use AddressCompletion\Contracts\AddressProvider;
use AddressCompletion\DTO\Address;

it('returns results from the first provider supporting the country', function () {
    $address = new Address(
        label: '10 Rue de Paris, 75001 Paris',
        street: '10 Rue de Paris',
        postcode: '75001',
        city: 'Paris',
    );
    $provider = Mockery::mock(AddressProvider::class);
    $provider->expects('supports')
        ->with('FR')
        ->once()
        ->andReturnTrue();
    $provider->expects('autocomplete')
        ->with('10 rue', 'FR', null)
        ->once()
        ->andReturn([$address]);

    $unusedProvider = Mockery::mock(AddressProvider::class);
    $unusedProvider->shouldNotReceive('supports');

    $manager = new AddressManager([$provider, $unusedProvider]);

    expect($manager->search('10 rue', 'fr'))->toEqual([$address]);
});

it('passes limit parameter to provider when specified', function () {
    $address = new Address(
        label: '10 Rue de Paris, 75001 Paris',
        street: '10 Rue de Paris',
        postcode: '75001',
        city: 'Paris',
    );
    $provider = Mockery::mock(AddressProvider::class);
    $provider->expects('supports')
        ->with('FR')
        ->once()
        ->andReturnTrue();
    $provider->expects('autocomplete')
        ->with('10 rue', 'FR', 5)
        ->once()
        ->andReturn([$address]);

    $manager = new AddressManager([$provider]);

    expect($manager->search('10 rue', 'fr', 5))->toEqual([$address]);
});

it('uses the configured default country', function () {
    $provider = Mockery::mock(AddressProvider::class);
    $provider->expects('supports')
        ->with('FR')
        ->once()
        ->andReturnTrue();
    $provider->expects('autocomplete')
        ->with('10 rue', 'FR', null)
        ->once()
        ->andReturn([]);

    $manager = new AddressManager([$provider]);

    expect($manager->search('10 rue'))->toBeArray()->toBeEmpty();
});

it('returns an empty array when no provider supports the country', function () {
    $provider = Mockery::mock(AddressProvider::class);
    $provider->expects('supports')
        ->with('DE')
        ->once()
        ->andReturnFalse();
    $provider->shouldNotReceive('autocomplete');

    $manager = new AddressManager([$provider]);

    expect($manager->search('10 rue', 'de'))->toBe([]);
});