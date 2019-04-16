<?php

$router->group(['prefix' => 'auth'], function () use ($router) {
  $router->post('signin', ['uses' => 'AuthController@signin']);
  $router->post('changePasswordAfterSignin', ['uses' => 'AuthController@changePasswortAfterSignin']);

  $router->post('refresh', ['middleware' => 'jwt.auth', 'uses' => 'AuthController@refresh']);

  //TODO: Forgot password
  /** Forgot password routes */
  $router->group(['prefix' => 'forgotPassword'], function () use($router) {
    $router->post('sendEmail', ['uses' => 'AuthController@sendForgotPaswordEmail']);
    $router->post('checkCode', ['uses' => 'AuthController@checkForgotPasswordCode']);
    $router->post('resetPassword', ['uses' => 'AuthController@resetPasswordAfterForgotPassword']);
  });
});