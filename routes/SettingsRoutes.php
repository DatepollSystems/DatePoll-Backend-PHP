<?php

use App\Http\Middleware\JwtMiddleware;
use App\Http\Middleware\SettingsPermissionMiddleware;

$router->group(['prefix' => 'settings'], function () use ($router) {
  $router->get('cinema', ['uses' => 'SettingsController@getCinemaFeatureIsEnabled']);
});

$router->group(['prefix' => 'settings/administration', 'middleware' => [JwtMiddleware::class, SettingsPermissionMiddleware::class]], function () use ($router) {
  $router->post('cinema', ['uses' => 'SettingsController@setCinemaFeatureIsEnabled']);
});