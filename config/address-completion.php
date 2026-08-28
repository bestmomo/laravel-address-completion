<?php

declare(strict_types=1);

use AddressCompletion\Providers\BanProvider;
use AddressCompletion\Providers\GeoapifyProvider;

return [

    'default_country' => 'FR',
    
    'fallback_provider' => 'geoapify',

    'limit' => 5,

    'providers' => [

        'ban' => [
            'class' => BanProvider::class,
            'url' => 'https://data.geopf.fr/geocodage/search',

            'countries' => [
                'FR',
                'GP',
                'MQ',
                'GF',
                'RE',
                'YT',
                'MF',
                'BL',
            ],
        ],

        'geoapify' => [
            'class' => GeoapifyProvider::class,
            'url' => 'https://api.geoapify.com/v1/geocode/autocomplete',
            'key' => env('GEOAPIFY_KEY', null),
        ],

    ],

    'cache' => [
        'enabled' => false,
        'ttl' => 3600,
    ],

];
