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

use App\Http\Middleware\Services\CinemaMiddleware;

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
    $router->post('signin', [
      'uses' => 'AuthController@signin'
    ]);
    $router->post('changePasswortAfterSignin', [
      'uses' => 'AuthController@changePasswortAfterSignin'
    ]);
    $router->post('refresh', [
      'middleware' => 'jwt.auth',
      'uses' => 'AuthController@refresh'
    ]);
    $router->group(['prefix' => 'forgotPassword'], function () use($router) {
      $router->post('sendEmail', [
        'uses' => 'AuthController@sendForgotPaswordEmail'
      ]);
      $router->post('checkCode', [
        'uses' => 'AuthController@checkForgotPasswordCode'
      ]);
      $router->post('resetPassword', [
        'uses' => 'AuthController@resetPasswordAfterForgotPassword'
      ]);
    });
  });

  $router->group(['prefix' => 'v1', 'middleware' => 'jwt.auth'], function () use ($router) {

    $router->group(['prefix' => 'user'], function () use ($router) {
      $router->get('myself', [
        'uses' => 'UserController@getMyself'
      ]);
      $router->put('myself', [
        'uses' => 'UserController@updateMyself'
      ]);

      $router->group(['prefix' => 'myself/changeEmail'], function () use ($router) {
        $router->get('oldEmailAddressVerification', [
          'uses' => 'UserController@oldEmailAddressVerification'
        ]);

        $router->post('oldEmailAddressVerificationCodeVerification', [
          'uses' => 'UserController@oldEmailAddressVerificationCodeVerification'
        ]);

        $router->post('newEmailAddressVerification', [
          'uses' => 'UserController@newEmailAddressVerification'
        ]);

        $router->post('newEmailAddressVerificationCodeVerification', [
          'uses' => 'UserController@newEmailAddressVerificationCodeVerification'
        ]);

        $router->post('changeEmailAddress', [
          'uses' => 'UserController@changeEmailAddress'
        ]);
      });

      $router->group(['prefix' => 'myself/changePassword'], function () use ($router) {
        $router->post('checkOldPassword', [
          'uses' => 'UserController@checkOldPassword'
        ]);

        $router->post('changePassword', [
          'uses' => 'UserController@changePassword'
        ]);
      });
    });

    $router->group(['prefix' => 'cinema', 'middleware' => CinemaMiddleware::class], function () use ($router) {
      /** Movie routes */
      $router->get('movie', [
        'uses' => 'CinemaControllers\MovieController@index'
      ]);

      $router->post('movie', [
        'uses' => 'CinemaControllers\MovieController@store'
      ]);

      $router->get('movie/{id}', [
        'uses' => 'CinemaControllers\MovieController@show'
      ]);

      $router->put('movie/{id}', [
        'uses' => 'CinemaControllers\MovieController@update'
      ]);

      $router->delete('movie/{id}', [
        'uses' => 'CinemaControllers\MovieController@destory'
      ]);

      $router->get('notShownMovies', [
        'uses' => 'CinemaControllers\MovieController@getNotShownMovies'
      ]);

      /** Year routes */
      $router->get('year', [
        'uses' => 'CinemaControllers\MovieYearController@index'
      ]);

      $router->post('year', [
        'uses' => 'CinemaControllers\MovieYearController@store'
      ]);

      $router->get('year/{id}', [
        'uses' => 'CinemaControllers\MovieYearController@show'
      ]);

      $router->put('year/{id}', [
        'uses' => 'CinemaControllers\MovieYearController@update'
      ]);

      $router->delete('year/{id}', [
        'uses' => 'CinemaControllers\MovieYearController@destory'
      ]);

    });

  });
});
