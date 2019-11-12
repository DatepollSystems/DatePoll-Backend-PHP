<?php

namespace Tests\Factories;

use App\Models\User\User;
use App\Models\User\UserEmailAddress;
use App\Models\User\UserTelephoneNumber;

class UserFactory
{

  public static function createUser($username) {
    $user = new User([
      'title' => 'Dr.',
      'username' => $username,
      'firstname' => 'Franz',
      'surname' => 'Anderson',
      'birthday' => '1980-01-02',
      'join_date' => '2001-03-12',
      'streetname' => 'Geisterstraße',
      'streetnumber' => '7',
      'zipcode' => 3730,
      'location' => 'Zauberstadt',
      'activated' => 1,
      'activity' => 'active',
      'password' => 'Null']);
    $user->save();

    for ($i = 111111; $i < 111115; $i++) {
      $phoneNumberToSave = new UserTelephoneNumber([
        'label' => 'Testnumber',
        'number' => $i,
        'user_id' => $user->id]);

      $phoneNumberToSave->save();
    }

    for ($i = 0; $i < 3; $i++) {
      $emailAddressToSave = new UserEmailAddress([
        'email' => 'test' . $i . '@testgmail.at',
        'user_id' => $user->id]);
      $emailAddressToSave->save();
    }

    return $user;
  }

  public static function findUserByUsername($username) {
    return User::where('username', '=', $username)
               ->first();
  }

  public static function findAndDeleteUser($username) {
    $user = User::where('username', '=', $username)
                ->first();
    if ($user != null) {
      $user->delete();
    }
  }

}
