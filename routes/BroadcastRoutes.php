<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\Broadcasts\BroadcastsAdministrationPermissionMiddleware;
use App\Http\Middleware\Broadcasts\BroadcastsFeatureMiddleware;

$router->group(['prefix' => 'broadcast', 'middleware' => [BroadcastsFeatureMiddleware::class]], function () use($router) {

  $router->get('', ['uses' => 'BroadcastControllers\BroadcastController@getRecentBroadcasts']);
  $router->get('{id}', ['uses' => 'BroadcastControllers\BroadcastController@getSingle']);

  /** Broadcast administration routes */
  $router->group([
    'prefix' => 'administration',
    'middleware' => [BroadcastsAdministrationPermissionMiddleware::class]],
    function () use ($router) {
      $router->get('broadcast', ['uses' => 'BroadcastControllers\BroadcastController@getAll']);
      $router->post('broadcast', ['uses' => 'BroadcastControllers\BroadcastController@create']);
      $router->get('broadcast/{id}', ['uses' => 'BroadcastControllers\BroadcastController@getSentReceiptReturnable']);
      $router->delete('broadcast/{id}', ['uses' => 'BroadcastControllers\BroadcastController@delete']);
    });
});
