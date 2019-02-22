<?php

use App\Http\Middleware\ManagementPermissionMiddleware;

$router->group(['prefix' => 'management', 'middleware' => [ManagementPermissionMiddleware::class]], function () use ($router) {
  /** Users routes */
  $router->get('users', ['uses' => 'ManagementControllers\UsersController@getAll']);
  $router->post('users', ['uses' => 'ManagementControllers\UsersController@create']);
  $router->get('users/{id}', ['uses' => 'ManagementControllers\UserController@getSingle']);
  $router->put('users/{id}', ['uses' => 'ManagementControllers\UserController@update']);
  $router->delete('users/{id}', ['uses' => 'ManagementControllers\UserController@delete']);
});