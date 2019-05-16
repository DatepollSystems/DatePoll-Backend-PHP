<?php

$router->group(['prefix' => 'auth'], function () use ($router) {
  $router->post('signin', ['uses' => 'AuthController@signin']);
  $router->post('changePasswordAfterSignin', ['uses' => 'AuthController@changePasswordAfterSignin']);
  $router->post('IamLoggedIn', ['uses' => 'AuthController@IamLoggedIn']);

  $router->post('refresh', ['middleware' => 'jwt.auth', 'uses' => 'AuthController@refresh']);

  /** Forgot password routes */
  $router->group(['prefix' => 'forgotPassword'], function () use($router) {
    $router->post('sendEmail', ['uses' => 'AuthController@sendForgotPasswordEmail']);
    $router->post('checkCode', ['uses' => 'AuthController@checkForgotPasswordCode']);
    $router->post('resetPassword', ['uses' => 'AuthController@resetPasswordAfterForgotPassword']);
  });
});