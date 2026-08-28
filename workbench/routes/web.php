<?php

use Illuminate\Support\Facades\Route;
use AddressCompletion\Facades\Address;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/search', function () {
    return Address::search(
        request('q'),
        request('country'),
        request('limit')
    );
});
