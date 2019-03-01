<?php

use App\Http\Middleware\ManagementPermissionMiddleware;

$router->group(['prefix' => 'management', 'middleware' => [ManagementPermissionMiddleware::class]], function () use ($router) {
  /** Users routes */
  $router->get('users', ['uses' => 'ManagementControllers\UsersController@getAll']);
  $router->post('users', ['uses' => 'ManagementControllers\UsersController@create']);
  $router->get('users/{id}', ['uses' => 'ManagementControllers\UsersController@getSingle']);
  $router->put('users/{id}', ['uses' => 'ManagementControllers\UsersController@update']);
  $router->delete('users/{id}', ['uses' => 'ManagementControllers\UsersController@delete']);

  /** Groups routes */
  $router->get('groups', ['uses' => 'ManagementControllers\GroupController@getAll']);
  $router->post('groups', ['uses' => 'ManagementControllers\GroupController@create']);
  $router->get('groups/{id}', ['uses' => 'ManagementControllers\GroupController@getSingle']);
  $router->put('groups/{id}', ['uses' => 'ManagementControllers\GroupController@update']);
  $router->delete('groups/{id}', ['uses' => 'ManagementControllers\GroupController@delete']);

  /** Subgroups routes */
  $router->get('subgroups', ['uses' => 'ManagementControllers\SubgroupController@getAll']);
  $router->post('subgroups', ['uses' => 'ManagementControllers\SubgroupController@create']);
  $router->get('subgroups/{id}', ['uses' => 'ManagementControllers\SubgroupController@getSingle']);
  $router->put('subgroups/{id}', ['uses' => 'ManagementControllers\SubgroupController@update']);
  $router->delete('subgroups/{id}', ['uses' => 'ManagementControllers\SubgroupController@delete']);
});