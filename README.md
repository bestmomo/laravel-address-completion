<div align="center">
    <h1>Address Completion</h1>
</div>

Address Completion provides a simple Laravel API for address autocomplete. It uses the French BAN API for French territories and Geoapify as a fallback for other countries.

## Installation

This package requires PHP 8.3 or later and Laravel 12 or 13.

You can install the package via Composer:

```bash
composer require bestmomo/laravel-address-completion
```

You may publish all of the package's resources at once:

```bash
php artisan vendor:publish --tag="laravel-address-completion"
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

### Cache

Address search results are cached automatically for 5 minutes to reduce calls to the BAN and Geoapify APIs. The cache uses Laravel's default cache store, so configure it in your application's cache settings when needed.

Cache entries are separated by provider, country, result limit and search query. To clear cached address results, clear the application's cache:

```bash
php artisan cache:clear
```

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
