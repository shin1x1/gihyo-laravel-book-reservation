<?php
$apiPrefix = '/api';

Route::get($apiPrefix . '/ping', function () {
    return Response::json('pong');
});

Route::filter('api_auth', '\Gihyo\BookReservation\Filter\ApiAuthFilter');
Route::pattern('reservation_code', '[a-zA-Z0-9\-]+');

Route::group(['before' => 'api_auth'], function () use ($apiPrefix) {
    $controller = '\Gihyo\BookReservation\Controller\ReservationController';
    Route::post($apiPrefix . '/reservation', $controller . '@create');
    Route::get($apiPrefix . '/reservations', $controller . '@index');
    Route::put($apiPrefix . '/reservation/{reservation_code}', $controller . '@update');
    Route::delete($apiPrefix . '/reservation/{reservation_code}', $controller . '@delete');
});
