<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

use App\Http\Middleware\Cinema\CinemaFeatureMiddleware;
use App\Http\Middleware\Cinema\CinemaPermissionMiddleware;

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['prefix' => 'api'], function () use ($router) {

  /** Setting routes */
  $router->group(['prefix' => 'settings'], function () use ($router) {
    $router->get('cinema', function () use ($router) {
      return response()->json(['msg' => 'Is cinema service enabled' ,'enabled' => env('APP_CINEMA_ENABLED', false)], 200);
    });
  });

  /** Auth routes */
  $router->group(['prefix' => 'auth'], function () use ($router) {
    $router->post('signin', ['uses' => 'AuthController@signin']);
    $router->post('changePasswortAfterSignin', ['uses' => 'AuthController@changePasswortAfterSignin']);

    $router->post('refresh', ['middleware' => 'jwt.auth', 'uses' => 'AuthController@refresh']);

    //TODO: Forgot password
    /** Forgot password routes */
    $router->group(['prefix' => 'forgotPassword'], function () use($router) {
      $router->post('sendEmail', ['uses' => 'AuthController@sendForgotPaswordEmail']);
      $router->post('checkCode', ['uses' => 'AuthController@checkForgotPasswordCode']);
      $router->post('resetPassword', ['uses' => 'AuthController@resetPasswordAfterForgotPassword']);
    });
  });

  $router->group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function () use ($router) {

    /** User routes */
    $router->group(['prefix' => 'user'], function () use ($router) {
      $router->get('myself', ['uses' => 'UserController@getMyself']);
      $router->put('myself', ['uses' => 'UserController@updateMyself']);

      /** Change email in settings routes */
      $router->group(['prefix' => 'myself/changeEmail'], function () use ($router) {
        $router->get('oldEmailAddressVerification', ['uses' => 'UserController@oldEmailAddressVerification']);
        $router->post('oldEmailAddressVerificationCodeVerification', ['uses' => 'UserController@oldEmailAddressVerificationCodeVerification']);
        $router->post('newEmailAddressVerification', ['uses' => 'UserController@newEmailAddressVerification']);
        $router->post('newEmailAddressVerificationCodeVerification', ['uses' => 'UserController@newEmailAddressVerificationCodeVerification']);
        $router->post('changeEmailAddress', ['uses' => 'UserController@changeEmailAddress']);
      });

      /** Change password in settings routes */
      $router->group(['prefix' => 'myself/changePassword'], function () use ($router) {
        $router->post('checkOldPassword', ['uses' => 'UserController@checkOldPassword']);
        $router->post('changePassword', ['uses' => 'UserController@changePassword']);
      });

      /** Change phone numbers */
      $router->post('myself/phoneNumber', ['uses' => 'UserController@addPhoneNumber']);
      $router->delete('myself/phoneNumber/{id}', ['uses' => 'UserController@removePhoneNumber']);
    });

    /** Management routes */
    $router->group(['prefix' => 'management'], function () use ($router) {
      /** Users routes */
      $router->get('users', ['uses' => 'ManagementControllers\UsersController@getAll']);
      $router->post('users', ['uses' => 'ManagementControllers\UsersController@create']);
      $router->get('users/{id}', ['uses' => 'ManagementControllers\UserController@getSingle']);
      $router->put('users/{id}', ['uses' => 'ManagementControllers\UserController@update']);
      $router->delete('users/{id}', ['uses' => 'ManagementControllers\UserController@delete']);
    });

    /** Cinema routes */
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
      $router->group([
        'prefix' => 'administration',
        'middleware' => [CinemaPermissionMiddleware::class]],
        function () use ($router) {
          /** Movie routes */
          $router->get('movie', ['uses' => 'CinemaControllers\MovieController@getAll']);
          $router->post('movie', ['uses' => 'CinemaControllers\MovieController@create']);
          $router->get('movie/{id}', ['uses' => 'CinemaControllers\MovieController@getSingle']);
          $router->put('movie/{id}', ['uses' => 'CinemaControllers\MovieController@update']);
          $router->delete('movie/{id}', ['uses' => 'CinemaControllers\MovieController@delete']);

          /** Year routes */
          $router->get('year', ['uses' => 'CinemaControllers\MovieYearController@index']);
          $router->post('year', ['uses' => 'CinemaControllers\MovieYearController@store']);
          $router->get('year/{id}', ['uses' => 'CinemaControllers\MovieYearController@show']);
          $router->put('year/{id}', ['uses' => 'CinemaControllers\MovieYearController@update']);
          $router->delete('year/{id}', ['uses' => 'CinemaControllers\MovieYearController@destory']);

        });
    });
  });
});
