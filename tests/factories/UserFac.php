<?php

namespace Tests\Factories;

use App\Models\User\User;
use App\Models\User\UserEmailAddress;
use App\Models\User\UserPermission;
use App\Models\User\UserTelephoneNumber;
use Firebase\JWT\JWT;

class UserFactory {
  public static function createUser($username, $administrator = true) {
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
      'bv_member' => 'not',
      'password' => 'Null', ]);
    $user->save();

    for ($i = 111111; $i < 111115; $i++) {
      $phoneNumberToSave = new UserTelephoneNumber([
        'label' => 'Testnumber',
        'number' => $i,
        'user_id' => $user->id, ]);

      $phoneNumberToSave->save();
    }

    for ($i = 0; $i < 3; $i++) {
      $emailAddressToSave = new UserEmailAddress([
        'email' => 'test' . $i . '@testgmail.at',
        'user_id' => $user->id, ]);
      $emailAddressToSave->save();
    }

    if ($administrator) {
      $permission = new UserPermission(['user_id' => $user->id, 'permission' => 'root.administration']);
      $permission->save();
    }

    return $user;
  }

  /**
   * Create a new token.
   *
   * @param int $userID
   * @return string
   */
  public static function getJWTTokenForUserId($userID) {
    $payload = ['iss' => 'lumen-jwt',// Issuer of the token
      'sub' => $userID,// Subject of the token
      'iat' => time(),// Time when JWT was issued.
      'exp' => time() + 60 * 60,// Expiration time
    ];

    // As you can see we are passing `JWT_SECRET` as the second parameter that will
    // be used to decode the token in the future.
    return JWT::encode($payload, env('JWT_SECRET'));
  }
}
