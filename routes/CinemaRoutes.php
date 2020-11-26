<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\Cinema\CinemaFeatureMiddleware;
use App\Http\Middleware\Cinema\CinemaPermissionMiddleware;

$router->group(['prefix' => 'cinema', 'middleware' => [CinemaFeatureMiddleware::class]], function () use ($router) {
  $router->get('notShownMovies', ['uses' => 'CinemaControllers\MovieController@getNotShownMovies']);

  /** Booking routes */
  $router->post('booking', ['uses' => 'CinemaControllers\MovieBookingController@bookTickets']);
  $router->delete('booking/{id}', ['uses' => 'CinemaControllers\MovieBookingController@cancelBooking']);

  /** Worker routes */
  $router->post('worker/{id}', ['uses' => 'CinemaControllers\MovieWorkerController@applyForWorker']);
  $router->delete('worker/{id}', ['uses' => 'CinemaControllers\MovieWorkerController@signOutForWorker']);
  $router->post('emergencyWorker/{id}', ['uses' => 'CinemaControllers\MovieWorkerController@applyForEmergencyWorker']);
  $router->delete('emergencyWorker/{id}', ['uses' => 'CinemaControllers\MovieWorkerController@signOutForEmergencyWorker']);
  $router->get('worker', ['uses' => 'CinemaControllers\MovieWorkerController@getMovies']);

  /** Movie administration routes */
  $router->group(
    [
      'prefix' => 'administration',
      'middleware' => [CinemaPermissionMiddleware::class], ],
    function () use ($router) {
      /** Movie routes */
      $router->get('movie', ['uses' => 'CinemaControllers\MovieController@getAll']);
      $router->post('movie', ['uses' => 'CinemaControllers\MovieController@create']);
      $router->get('movie/{id}', ['uses' => 'CinemaControllers\MovieController@getSingle']);
      $router->put('movie/{id}', ['uses' => 'CinemaControllers\MovieController@update']);
      $router->delete('movie/{id}', ['uses' => 'CinemaControllers\MovieController@delete']);

      $router->post('movie/{id}/bookForUsers', ['uses' => 'CinemaControllers\MovieBookingController@bookForUsers']);
      $router->post('movie/{id}/cancelBookingForUsers', ['uses' => 'CinemaControllers\MovieBookingController@cancelBookingForUsers']);

      /** Year routes */
      $router->get('year', ['uses' => 'CinemaControllers\MovieYearController@getAll']);
      $router->post('year', ['uses' => 'CinemaControllers\MovieYearController@create']);
      $router->get('year/{id}', ['uses' => 'CinemaControllers\MovieYearController@getSingle']);
      $router->put('year/{id}', ['uses' => 'CinemaControllers\MovieYearController@update']);
      $router->delete('year/{id}', ['uses' => 'CinemaControllers\MovieYearController@delete']);
    }
  );
});
