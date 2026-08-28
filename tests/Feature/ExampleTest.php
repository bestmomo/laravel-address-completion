<?php

declare(strict_types=1);

use AddressCompletion\AddressManager;

it('resolves the singleton', function () {
    expect(app(AddressManager::class))->toBeInstanceOf(AddressManager::class);
});

it('returns the same instance from the container', function () {
    expect(app(AddressManager::class))->toBe(app(AddressManager::class));
});

it('merges the package config', function () {
    expect(config('address-completion.default_country'))->toBe('FR');
});
