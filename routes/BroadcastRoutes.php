<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\Broadcasts\BroadcastsAdministrationPermissionMiddleware;
use App\Http\Middleware\Broadcasts\BroadcastsFeatureMiddleware;

$router->group(['prefix' => 'broadcast', 'middleware' => [BroadcastsFeatureMiddleware::class]], function () use($router) {

  $router->get('', ['uses' => 'BroadcastControllers\BroadcastUserController@getAll']);
  $router->get('{id}', ['uses' => 'BroadcastControllers\BroadcastUserController@getSingle']);

  /** Broadcast administration routes */
  $router->group([
    'prefix' => 'administration',
    'middleware' => [BroadcastsAdministrationPermissionMiddleware::class]],
    function () use ($router) {
      $router->get('broadcast', ['uses' => 'BroadcastControllers\BroadcastController@getAll']);
      $router->post('broadcast', ['uses' => 'BroadcastControllers\BroadcastController@create']);
      $router->get('broadcast/{id}', ['uses' => 'BroadcastControllers\BroadcastController@getSentReceiptReturnable']);
      $router->delete('broadcast/{id}', ['uses' => 'BroadcastControllers\BroadcastController@delete']);

      /** Attachments */
      $router->post('attachment', ['uses' => 'BroadcastControllers\BroadcastController@attachmentsUpload']);
      $router->delete('attachment/{id}', ['uses' => 'BroadcastControllers\BroadcastController@attachmentDelete']);

      /** Drafts */
      $router->get('draft', ['uses' => 'BroadcastControllers\BroadcastDraftController@getAll']);
      $router->post('draft', ['uses' => 'BroadcastControllers\BroadcastDraftController@create']);
      $router->put('draft/{id}', ['uses' => 'BroadcastControllers\BroadcastDraftController@update']);
      $router->get('draft/{id}', ['uses' => 'BroadcastControllers\BroadcastDraftController@getSingle']);
      $router->delete('draft/{id}', ['uses' => 'BroadcastControllers\BroadcastDraftController@delete']);
    });
});
