<?php /** @noinspection PhpUndefinedVariableInspection */

use App\Http\Middleware\SeatReservation\SeatReservationAdministrationPermissionMiddleware;
use App\Http\Middleware\SeatReservation\SeatReservationFeatureMiddleware;

$router->group(
  ['prefix' => 'seatReservation', 'middleware' => [SeatReservationFeatureMiddleware::class]],
  function () use ($router) {
    $router->get('all', ['uses' => 'SeatReservationControllers\UserSeatReservationController@getAllPlaceReservations']);
    $router->get('upcoming', ['uses' => 'SeatReservationControllers\UserSeatReservationController@getUpcomingPlaceReservations']);

    $router->get('', ['uses' => 'SeatReservationControllers\UserSeatReservationController@getUserReservations']);
    $router->post('', ['uses' => 'SeatReservationControllers\UserSeatReservationController@createPlaceReservation']);
    $router->put('{id}', ['uses' => 'SeatReservationControllers\UserSeatReservationController@updatePlaceReservation']);

    $router->get('place', ['uses' => 'SeatReservationControllers\PlaceController@getAll']);

    /** Seat reservation administration routes */
    $router->group(
      ['prefix' => 'administration',
        'middleware' => [SeatReservationAdministrationPermissionMiddleware::class], ],
      function () use ($router) {
        $router->post('place', ['uses' => 'SeatReservationControllers\PlaceController@create']);
        $router->put('place/{id}', ['uses' => 'SeatReservationControllers\PlaceController@update']);
        $router->delete('place/{id}', ['uses' => 'SeatReservationControllers\PlaceController@delete']);
      }
    );
  }
);
