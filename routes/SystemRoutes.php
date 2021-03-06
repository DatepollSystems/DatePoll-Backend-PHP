<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\SettingsPermissionMiddleware;
use App\Http\Middleware\System\JobsPermissionMiddleware;

$router->group(['prefix' => 'settings'], function () use ($router) {
  $router->get('openweathermap/key', ['uses' => 'SystemControllers\SettingsController@getOpenWeatherMapKey']);
  $router->get('openweathermap/cinemaCityId', ['uses' => 'SystemControllers\SettingsController@getOpenWeatherMapCinemaCityId']);
  $router->get('alert', ['uses' => 'SystemControllers\SettingsController@getAlert']);
  $router->get('jitsiInstanceUrl', ['uses' => 'SystemControllers\SettingsController@getJitsiInstanceUrl']);
});

$router->group(['prefix' => 'system'], function () use ($router) {
  $router->group([
    'prefix' => 'settings',
    'middleware' => [SettingsPermissionMiddleware::class], ], function () use ($router) {
      $router->get('broadcast/forwardIncomingEmailsEmailAddresses', ['uses' => 'SystemControllers\SettingsController@getBroadcastsProcessIncomingEmailsForwardingEmailAddresses']);

      $router->post('cinema', ['uses' => 'SystemControllers\SettingsController@setCinemaFeatureIsEnabled']);
      $router->post('events', ['uses' => 'SystemControllers\SettingsController@setEventsFeatureIsEnabled']);
      $router->post('broadcast', ['uses' => 'SystemControllers\SettingsController@setBroadcastFeatureIsEnabled']);
      $router->post('broadcast/processIncomingEmails', ['uses' => 'SystemControllers\SettingsController@setBroadcastProcessIncomingEmailsFeatureIsEnabled']);
      $router->post('broadcast/forwardIncomingEmails', ['uses' => 'SystemControllers\SettingsController@setBroadcastsProcessIncomingEmailsForwardingIsEnabled']);
      $router->post('broadcast/forwardIncomingEmailsEmailAddresses', ['uses' => 'SystemControllers\SettingsController@setBroadcastsProcessIncomingEmailsForwardingEmailAddresses']);
      $router->post('name', ['uses' => 'SystemControllers\SettingsController@setCommunityName']);
      $router->post('communityUrl', ['uses' => 'SystemControllers\SettingsController@setCommunityUrl']);
      $router->post('description', ['uses' => 'SystemControllers\SettingsController@setCommunityDescription']);
      $router->post('imprint', ['uses' => 'SystemControllers\SettingsController@setCommunityImprint']);
      $router->post('privacyPolicy', ['uses' => 'SystemControllers\SettingsController@setCommunityPrivacyPolicy']);
      $router->post('openweathermap/key', ['uses' => 'SystemControllers\SettingsController@setOpenWeatherMapKey']);
      $router->post('openweathermap/cinemaCityId', ['uses' => 'SystemControllers\SettingsController@setOpenWeatherMapCinemaCityId']);
      $router->post('jitsiInstanceUrl', ['uses' => 'SystemControllers\SettingsController@setJitsiInstanceUrl']);
      $router->post('url', ['uses' => 'SystemControllers\SettingsController@setUrl']);
      $router->post('alert', ['uses' => 'SystemControllers\SettingsController@setAlert']);
    });

  /** Job routes */
  $router->group([
    'prefix' => 'jobs',
    'middleware' => [JobsPermissionMiddleware::class], ], function () use ($router) {
      $router->get('', ['uses' => 'SystemControllers\JobController@getUndoneJobs']);
    });
});
