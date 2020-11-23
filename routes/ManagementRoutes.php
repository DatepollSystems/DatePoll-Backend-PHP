<?php
/** @noinspection PhpUndefinedVariableInspection */

/** @noinspection PhpUndefinedMethodInspection */

use App\Http\Middleware\ManagementPermissionMiddleware;

$router->group([
  'prefix' => 'management',
  'middleware' => [ManagementPermissionMiddleware::class], ], function () use ($router) {
    /** Users routes */
    $router->get('users', ['uses' => 'ManagementControllers\UsersController@getAll']);
    $router->post('users', ['uses' => 'ManagementControllers\UsersController@create']);
    $router->get('users/{id}', ['uses' => 'ManagementControllers\UsersController@getSingle']);
    $router->put('users/changePassword/{id}', ['uses' => 'ManagementControllers\UsersController@changePassword']);
    $router->put('users/{id}', ['uses' => 'ManagementControllers\UsersController@update']);
    $router->delete('users/{id}', ['uses' => 'ManagementControllers\UsersController@delete']);
    $router->post('users/activate', ['uses' => 'ManagementControllers\UsersController@activateAll']);
    $router->get('export/users', ['uses' => 'ManagementControllers\UsersController@export']);
    $router->get('changes/users', ['uses' => 'ManagementControllers\UsersController@getAllUserChanges']);
    $router->delete('changes/users/{id}', ['uses' => 'ManagementControllers\UsersController@deleteUserChange']);
    $router->get('deleted/users', ['uses' => 'ManagementControllers\UsersController@getDeletedUsers']);
    $router->delete('deleted/users', ['uses' => 'ManagementControllers\UsersController@deleteAllDeletedUsers']);

    /** Groups routes */
    $router->get('groups', ['uses' => 'ManagementControllers\GroupController@getAll']);
    $router->post('groups', ['uses' => 'ManagementControllers\GroupController@create']);
    $router->get('groups/{id}', ['uses' => 'ManagementControllers\GroupController@getSingle']);
    $router->put('groups/{id}', ['uses' => 'ManagementControllers\GroupController@update']);
    $router->delete('groups/{id}', ['uses' => 'ManagementControllers\GroupController@delete']);
    $router->post('groups/addUser', ['uses' => 'ManagementControllers\GroupController@addUser']);
    $router->post('groups/removeUser', ['uses' => 'ManagementControllers\GroupController@removeUser']);
    $router->post('groups/updateUser', ['uses' => 'ManagementControllers\GroupController@updateUser']);
    $router->get('groups/joined/{userID}', ['uses' => 'ManagementControllers\GroupController@joined']);
    $router->get('groups/free/{userID}', ['uses' => 'ManagementControllers\GroupController@free']);

    /** Subgroups routes */
    $router->get('subgroups', ['uses' => 'ManagementControllers\SubgroupController@getAll']);
    $router->post('subgroups', ['uses' => 'ManagementControllers\SubgroupController@create']);
    $router->get('subgroups/{id}', ['uses' => 'ManagementControllers\SubgroupController@getSingle']);
    $router->put('subgroups/{id}', ['uses' => 'ManagementControllers\SubgroupController@update']);
    $router->delete('subgroups/{id}', ['uses' => 'ManagementControllers\SubgroupController@delete']);
    $router->post('subgroups/addUser', ['uses' => 'ManagementControllers\SubgroupController@addUser']);
    $router->post('subgroups/removeUser', ['uses' => 'ManagementControllers\SubgroupController@removeUser']);
    $router->post('subgroups/updateUser', ['uses' => 'ManagementControllers\SubgroupController@updateUser']);
    $router->get('subgroups/joined/{userID}', ['uses' => 'ManagementControllers\SubgroupController@joined']);
    $router->get('subgroups/free/{userID}', ['uses' => 'ManagementControllers\SubgroupController@free']);

    /** Performance badges routes */
    $router->get('performanceBadges', ['uses' => 'ManagementControllers\PerformanceBadgeController@getAll']);
    $router->post('performanceBadges', ['uses' => 'ManagementControllers\PerformanceBadgeController@create']);
    $router->get('performanceBadges/{id}', ['uses' => 'ManagementControllers\PerformanceBadgeController@getSingle']);
    $router->put('performanceBadges/{id}', ['uses' => 'ManagementControllers\PerformanceBadgeController@update']);
    $router->delete('performanceBadges/{id}', ['uses' => 'ManagementControllers\PerformanceBadgeController@delete']);

    /** Instrument routes */
    $router->get('instruments', ['uses' => 'ManagementControllers\InstrumentController@getAll']);
    $router->post('instruments', ['uses' => 'ManagementControllers\InstrumentController@create']);
    $router->get('instruments/{id}', ['uses' => 'ManagementControllers\InstrumentController@getSingle']);
    $router->put('instruments/{id}', ['uses' => 'ManagementControllers\InstrumentController@update']);
    $router->delete('instruments/{id}', ['uses' => 'ManagementControllers\InstrumentController@delete']);

    $router->post('performanceBadgeWithInstrument', ['uses' => 'ManagementControllers\PerformanceBadgeController@addPerformanceBadgeForUserWithInstrument']);
    $router->delete('performanceBadgeWithInstrument/{id}', ['uses' => 'ManagementControllers\PerformanceBadgeController@removePerformanceBadgeForUserWithInstrument']);
    $router->get('performanceBadgesForUser/{id}', ['uses' => 'ManagementControllers\PerformanceBadgeController@performanceBadgesForUser']);

    /** Badge routes */
    $router->get('yearBadge/{year}', ['uses' => 'ManagementControllers\BadgeController@getYearBadges']);
    $router->get('badges', ['uses' => 'ManagementControllers\BadgeController@getAll']);
    $router->post('badges', ['uses' => 'ManagementControllers\BadgeController@create']);
    $router->delete('badges/{id}', ['uses' => 'ManagementControllers\BadgeController@delete']);
    $router->post('badgeForUser', ['uses' => 'ManagementControllers\BadgeController@addUserBadge']);
    $router->delete('badgeForUser/{id}', ['uses' => 'ManagementControllers\BadgeController@removeUserBadge']);
    $router->get('badgesForUser/{id}', ['uses' => 'ManagementControllers\BadgeController@userBadgesForUser']);
  });
