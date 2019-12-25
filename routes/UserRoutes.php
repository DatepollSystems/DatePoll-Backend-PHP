<?php /** @noinspection PhpUndefinedVariableInspection */

$router->group(['prefix' => 'user'], function () use ($router) {
  /** Home page route */
  $router->get('homepage', ['uses' => 'UserControllers\UserController@homepage']);

  /** Myself routes */
  $router->get('myself', ['uses' => 'UserControllers\UserController@getMyself']);
  $router->put('myself', ['uses' => 'UserControllers\UserController@updateMyself']);

  $router->group(['prefix' => 'myself'], function () use ($router) {
    /** Change password */
    $router->group(['prefix' => 'changePassword'], function () use ($router) {
      $router->post('checkOldPassword', ['uses' => 'UserControllers\UserChangePasswordController@checkOldPassword']);
      $router->post('changePassword', ['uses' => 'UserControllers\UserChangePasswordController@changePassword']);
    });

    /** Email addresses */
    $router->post('changeEmailAddresses', ['uses' => 'UserControllers\UserChangeEmailController@changeEmailAddresses']);

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

    /** Settings */
    $router->group(['prefix' => 'settings'], function () use ($router) {
      $router->get('shareBirthday', ['uses' => 'UserControllers\UserSettingsController@getShareBirthday']);
      $router->post('shareBirthday', ['uses' => 'UserControllers\UserSettingsController@setShareBirthday']);
    });
  });
});