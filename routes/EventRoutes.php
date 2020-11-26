<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\Events\EventsAdministrationPermissionMiddleware;
use App\Http\Middleware\Events\EventsFeatureMiddleware;

$router->group(['prefix' => 'avent', 'middleware' => [EventsFeatureMiddleware::class]], function () use ($router) {
  $router->get('', ['uses' => 'EventControllers\EventListController@getOpenEvents']);

  $router->post('vote', ['uses' => 'EventControllers\EventVoteController@vote']);
  $router->delete('vote/{id}', ['uses' => 'EventControllers\EventVoteController@removeVoting']);

  // Get single event route out of EventsAdministrationPermissionMiddleware because the results are in this response
  $router->get('{id}', ['uses' => 'EventControllers\EventController@getSingle']);

  /** Event administration routes */
  $router->group(
    [
      'prefix' => 'administration',
      'middleware' => [EventsAdministrationPermissionMiddleware::class], ],
    function () use ($router) {
      /** Event routes */
      $router->get('avent', ['uses' => 'EventControllers\EventController@getEventsOrderedByDate']);
      $router->get('avent/years', ['uses' => 'EventControllers\EventController@getYearsOfEvents']);
      $router->get('avent/{year}', ['uses' => 'EventControllers\EventController@getEventsOrderedByDate']);
      $router->post('avent', ['uses' => 'EventControllers\EventController@create']);
      $router->put('avent/{id}', ['uses' => 'EventControllers\EventController@update']);
      $router->delete('avent/{id}', ['uses' => 'EventControllers\EventController@delete']);

      $router->post('avent/{id}/voteForUsers', ['uses' => 'EventControllers\EventVoteController@voteForUsers']);
      $router->post('avent/{id}/cancelVotingForUsers', ['uses' => 'EventControllers\EventVoteController@cancelVotingForUsers']);

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
      $router->delete('standardDecision/{id}', ['uses' => 'EventControllers\StandardDecisionController@delete']);

      /** Standard locations routes */
      $router->get('standardLocation', ['uses' => 'EventControllers\StandardLocationController@getAll']);
      $router->post('standardLocation', ['uses' => 'EventControllers\StandardLocationController@create']);
      $router->get('standardLocation/{id}', ['uses' => 'EventControllers\StandardLocationController@getSingle']);
      $router->delete('standardLocation/{id}', ['uses' => 'EventControllers\StandardLocationController@delete']);
    }
  );
});
