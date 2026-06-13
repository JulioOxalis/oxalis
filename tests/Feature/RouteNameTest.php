<?php

use Illuminate\Support\Facades\Route;

it('does not reserve laravel auth route names from package routes', function () {
    expect(Route::has('login'))->toBeFalse();
    expect(Route::has('register'))->toBeFalse();
    expect(Route::has('logout'))->toBeFalse();

    expect(Route::has('oxalis.redirect.login'))->toBeTrue();
    expect(Route::has('oxalis.redirect.register'))->toBeTrue();
    expect(Route::has('oxalis.redirect.logout'))->toBeTrue();
});
