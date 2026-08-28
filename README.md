<div align="center">
    <h1>Address Completion</h1>
</div>

Address Completion provides a simple Laravel API for address autocomplete. It uses the French BAN API for French territories and Geoapify as a fallback for other countries.

<p align="center">
    <a href="https://packagist.org/packages/bestmomo/address-completion"><img src="https://img.shields.io/packagist/v/bestmomo/address-completion.svg?style=flat-square" alt="Packagist"></a>
    <a href="https://packagist.org/packages/bestmomo/address-completion"><img src="https://img.shields.io/packagist/php-v/bestmomo/address-completion.svg?style=flat-square" alt="PHP from Packagist"></a>
    <a href="https://packagist.org/packages/bestmomo/address-completion"><img src="https://badge.laravel.cloud/badge/bestmomo/address-completion?style=flat" alt="Laravel versions"></a>
    <a href="https://github.com/bestmomo/address-completion/actions"><img alt="GitHub Workflow Status (main)" src="https://img.shields.io/github/actions/workflow/status/bestmomo/address-completion/tests.yml?branch=main&label=Tests&style=flat-square"></a>
    <a href="https://packagist.org/packages/bestmomo/address-completion"><img src="https://img.shields.io/packagist/dt/bestmomo/address-completion.svg?style=flat-square" alt="Total Downloads"></a>
</p>

## Installation

This package requires PHP 8.3 or later and Laravel 12 or 13.

You can install the package via Composer:

```bash
composer require bestmomo/address-completion
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="address-completion"
```

Or, you may publish each resource individually:

### Publishing the Configuration File

```bash
php artisan vendor:publish --tag="address-completion-config"
```

The configuration file is created at `config/address-completion.php`.

### Geoapify API key

Geoapify is used for countries that are not supported by the BAN provider. Add your API key to `.env`:

```dotenv
GEOAPIFY_KEY=your-api-key
```

You can get an API key from [Geoapify](https://www.geoapify.com/).

## Usage

Use the `Address` facade to search for addresses:

```php
use AddressCompletion\Facades\Address;

$addresses = Address::search('10 rue de Paris');

foreach ($addresses as $address) {
    echo $address->label;
}
```

The country is optional and defaults to `FR`. It accepts an ISO 3166-1 alpha-2 country code:

```php
$addresses = Address::search('1600 Pennsylvania Avenue', 'US', limit: 8);
```

Each result is an `AddressCompletion\DTO\Address` object with these properties:

| Property   | Description                                 |
| ---------- | ------------------------------------------- |
| `label`    | Formatted address label                     |
| `street`   | Street name and house number when available |
| `postcode` | Postal code                                 |
| `city`     | City or locality                            |

Queries shorter than five characters return an empty array. Network or provider errors are reported and also return an empty array, so autocomplete can fail gracefully.

### Configuration

The published configuration file lets you change the default country, result limit, provider URLs and the countries handled by the BAN provider:

```php
// config/address-completion.php
'default_country' => 'FR',
'limit' => 5,
```

The BAN provider is selected automatically for configured French territories. Geoapify handles all other countries and requires `GEOAPIFY_KEY`.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Thank you for considering contributing to Address Completion! Please review our [contributing guide](.github/CONTRIBUTING.md) to get started.

## Security Vulnerabilities

Please review [our security policy](.github/SECURITY.md) on how to report security vulnerabilities.

## Credits

- [bestmomo](https://github.com/bestmomo)
- [All Contributors](../../contributors)

## License

Address Completion is open-sourced software licensed under the [MIT license](LICENSE.md).
