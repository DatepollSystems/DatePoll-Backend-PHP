<?php

$router->group(['prefix' => 'settings'], function () use ($router) {
  $router->get('cinema', function () use ($router) {
    return response()->json(['msg' => 'Is cinema service enabled' ,'enabled' => env('APP_CINEMA_ENABLED', false)], 200);
  });
});