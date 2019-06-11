<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\Events\EventsAdministrationPermissionMiddleware;
use App\Http\Middleware\Events\EventsFeatureMiddleware;

$router->group(['prefix' => 'event', 'middleware' => [EventsFeatureMiddleware::class]], function () use($router) {

  //TODO:THIS
  $router->get('open', ['uses' => 'EventControllers\EventListController@getOpenEvents']);
  $router->get('closed', ['uses' => 'EventControllers\EventListController@getClosedEvents']);

  $router->post('vote', ['uses' => 'EventControllers\EventVoteController@vote']);
  $router->delete('vote', ['uses' => 'EventControllers\EventVoteController@removeVoting']);

  // Get single event route out of EventsAdministrationPermissionMiddleware because the results are in this response
  $router->get('{id}', ['uses' => 'EventControllers\EventController@getSingle']);

  /** Event administration routes */
  $router->group([
    'prefix' => 'administration',
    'middleware' => [EventsAdministrationPermissionMiddleware::class]],
    function () use ($router) {
      /** Event routes */
      $router->get('event', ['uses' => 'EventControllers\EventController@getAll']);
      $router->post('event', ['uses' => 'EventControllers\EventController@create']);
      $router->put('event/{id}', ['uses' => 'EventControllers\EventController@update']);
      $router->delete('event/{id}', ['uses' => 'EventControllers\EventController@delete']);

      $router->post('addGroupToEvent', ['uses' => 'EventControllers\EventGroupController@addGroupToEvent']);
      $router->post('addSubgroupToEvent', ['uses' => 'EventControllers\EventGroupController@addSubgroupToEvent']);
      $router->post('removeGroupFromEvent', ['uses' => 'EventControllers\EventGroupController@removeGroupFromEvent']);
      $router->post('removeSubgroupFromEvent', ['uses' => 'EventControllers\EventGroupController@removeSubgroupFromEvent']);

      $router->get('group/joined/{id}', ['uses' => 'EventControllers\EventGroupController@groupJoined']);
      $router->get('group/free/{id}', ['uses' => 'EventControllers\EventGroupController@groupFree']);
      $router->get('subgroup/joined/{id}', ['uses' => 'EventControllers\EventGroupController@subgroupJoined']);
      $router->get('subgroup/free/{id}', ['uses' => 'EventControllers\EventGroupController@subgroupFree']);

      /** Standard decision routes */
      $router->get('standardDecision', ['uses' => 'EventControllers\StandardDecisionController@getAll']);
      $router->post('standardDecision', ['uses' => 'EventControllers\StandardDecisionController@create']);
      $router->get('standardDecision/{id}', ['uses' => 'EventControllers\StandardDecisionController@getSingle']);
      $router->put('standardDecision/{id}', ['uses' => 'EventControllers\StandardDecisionController@update']);
      $router->delete('standardDecision/{id}', ['uses' => 'EventControllers\StandardDecisionController@delete']);


    });
});
