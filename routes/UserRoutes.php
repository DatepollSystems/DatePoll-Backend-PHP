<?php

$router->group(['prefix' => 'user'], function () use ($router) {
  /** Home page route */
  $router->get('homepage', ['uses' => 'UserControllers\UserController@homepage']);

  /** Myself routes */
  $router->get('myself', ['uses' => 'UserControllers\UserController@getMyself']);
  $router->put('myself', ['uses' => 'UserControllers\UserController@updateMyself']);

  $router->group(['prefix' => 'myself'], function () use ($router) {
    /** Change email */
    $router->group(['prefix' => 'changeEmail'], function () use ($router) {
      $router->get('oldEmailAddressVerification', ['uses' => 'UserControllers\UserChangeEmailController@oldEmailAddressVerification']);
      $router->post('oldEmailAddressVerificationCodeVerification', ['uses' => 'UserControllers\UserChangeEmailController@oldEmailAddressVerificationCodeVerification']);
      $router->post('newEmailAddressVerification', ['uses' => 'UserControllers\UserChangeEmailController@newEmailAddressVerification']);
      $router->post('newEmailAddressVerificationCodeVerification', ['uses' => 'UserControllers\UserChangeEmailController@newEmailAddressVerificationCodeVerification']);
      $router->post('changeEmailAddress', ['uses' => 'UserControllers\UserChangeEmailController@changeEmailAddress']);
    });

    /** Change password */
    $router->group(['prefix' => 'changePassword'], function () use ($router) {
      $router->post('checkOldPassword', ['uses' => 'UserControllers\UserChangePasswordController@checkOldPassword']);
      $router->post('changePassword', ['uses' => 'UserControllers\UserChangePasswordController@changePassword']);
    });

    /** Change phone numbers */
    $router->post('phoneNumber', ['uses' => 'UserControllers\UserChangePhoneNumberController@addPhoneNumber']);
    $router->delete('phoneNumber/{id}', ['uses' => 'UserControllers\UserChangePhoneNumberController@removePhoneNumber']);

    /** Token */
    $router->group(['prefix' => 'token'], function () use ($router) {
      $router->get('calendar', ['uses' => 'UserControllers\UserTokenController@getCalendarToken']);
      $router->delete('calendar', ['uses' => 'UserControllers\UserTokenController@resetCalendarToken']);
    });

    /** Session management */
    $router->get('session', ['uses' => 'UserControllers\UserTokenController@getAllSessions']);
    $router->post('session/logoutCurrentSession', ['uses' => 'UserControllers\UserTokenController@logoutCurrentSession']);
    $router->delete('session/{id}', ['uses' => 'UserControllers\UserTokenController@removeSession']);
  });
});