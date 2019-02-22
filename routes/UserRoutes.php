<?php

$router->group(['prefix' => 'user'], function () use ($router) {
  /** Home page route */
  $router->get('homepage', ['uses' => 'UserControllers\UserController@homepage']);

  /** Myself routes */
  $router->get('myself', ['uses' => 'UserControllers\UserController@getMyself']);
  $router->put('myself', ['uses' => 'UserControllers\UserController@updateMyself']);

  /** Change email */
  $router->group(['prefix' => 'myself/changeEmail'], function () use ($router) {
    $router->get('oldEmailAddressVerification', ['uses' => 'UserControllers\UserChangeEmailController@oldEmailAddressVerification']);
    $router->post('oldEmailAddressVerificationCodeVerification', ['uses' => 'UserControllers\UserChangeEmailController@oldEmailAddressVerificationCodeVerification']);
    $router->post('newEmailAddressVerification', ['uses' => 'UserControllers\UserChangeEmailController@newEmailAddressVerification']);
    $router->post('newEmailAddressVerificationCodeVerification', ['uses' => 'UserControllers\UserChangeEmailController@newEmailAddressVerificationCodeVerification']);
    $router->post('changeEmailAddress', ['uses' => 'UserControllers\UserChangeEmailController@changeEmailAddress']);
  });

  /** Change password */
  $router->group(['prefix' => 'myself/changePassword'], function () use ($router) {
    $router->post('checkOldPassword', ['uses' => 'UserControllers\UserChangePasswordController@checkOldPassword']);
    $router->post('changePassword', ['uses' => 'UserControllers\UserChangePasswordController@changePassword']);
  });

  /** Change phone numbers */
  $router->post('myself/phoneNumber', ['uses' => 'UserControllers\UserChangePhoneNumberController@addPhoneNumber']);
  $router->delete('myself/phoneNumber/{id}', ['uses' => 'UserControllers\UserChangePhoneNumberController@removePhoneNumber']);
});