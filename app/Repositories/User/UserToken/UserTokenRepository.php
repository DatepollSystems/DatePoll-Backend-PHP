<?php

namespace App\Repositories\User\UserToken;

use App\Models\User\User;
use App\Models\User\UserToken;
use App\Utils\Generator;
use Exception;

class UserTokenRepository implements IUserTokenRepository {
  /**
   * @param User $user
   * @param string $token
   * @param string $purpose
   * @param string|null $description
   * @return UserToken|null
   */
  public function createUserToken(User $user, string $token, string $purpose, ?string $description = null): ?UserToken {
    $userToken = new UserToken([
      'user_id' => $user->id,
      'token' => $token,
      'purpose' => $purpose,
      'description' => $description, ]);

    if (! $userToken->save()) {
      return null;
    } else {
      return $userToken;
    }
  }

  /**
   * @param UserToken $userToken
   * @return UserToken|null
   */
  public function deleteUserToken(UserToken $userToken): ?UserToken {
    try {
      if (! $userToken->delete()) {
        return $userToken;
      }
    } catch (Exception) {
      return null;
    }

    return null;
  }

  /**
   * @param int $length
   * @return string
   */
  public function generateUniqueRandomToken(int $length): string {
    while (true) {
      $randomToken = Generator::getRandomMixedNumberAndABCToken($length);

      if (UserToken::where('token', $randomToken)
        ->first() == null) {
        break;
      }
    }

    return $randomToken;
  }

  /**
   * @param int $id
   * @param User $user
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByIdAndUserAndPurpose(int $id, User $user, string $purpose): ?UserToken {
    return UserToken::where('purpose', $purpose)
      ->where('user_id', $user->id)
      ->where('id', $id)->first();
  }

  /**
   * @param string $token
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByTokenAndPurpose(string $token, string $purpose): ?UserToken {
    return UserToken::where('token', $token)
      ->where('purpose', $purpose)
      ->first();
  }

  /**
   * @param User $user
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByUserAndPurpose(User $user, string $purpose): ?UserToken {
    return UserToken::where('user_id', $user->id)
      ->where('purpose', $purpose)
      ->first();
  }

  /**
   * @param User $user
   * @param string $token
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByUserAndTokenAndPurpose(User $user, string $token, string $purpose): ?UserToken {
    return UserToken::where('user_id', $user->id)
      ->where('token', $token)
      ->where('purpose', $purpose)
      ->first();
  }

  /**
   * @param User $user
   * @param string $purpose
   * @return UserToken[]|null
   */
  public function getUserTokensByUserAndPurposeOrderedByDate(User $user, string $purpose): ?array {
    return UserToken::where('user_id', $user->id)
      ->where('purpose', $purpose)
      ->orderBy('updated_at', 'ASC')
      ->get()->all();
  }
}
