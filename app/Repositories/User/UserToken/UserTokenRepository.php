<?php

namespace App\Repositories\User\UserToken;

use App\Models\User\User;
use App\Models\User\UserToken;
use Exception;

class UserTokenRepository implements IUserTokenRepository {
  public function createUserToken(User $user, string $token, string $purpose, $description = null) {
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

  public function deleteUserToken(UserToken $userToken) {
    try {
      if (! $userToken->delete()) {
        return $userToken;
      }
    } catch (Exception $e) {
      return $userToken;
    }

    return null;
  }

  public function generateUniqueRandomToken(int $length) {
    $randomToken = '';
    while (true) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomToken = '';
      for ($i = 0; $i < $length; $i++) {
        $randomToken .= $characters[rand(0, $charactersLength - 1)];
      }

      if (UserToken::where('token', $randomToken)
        ->first() == null) {
        break;
      }
    }

    return $randomToken;
  }

  public function getUserTokenByIdAndUserAndPurpose(int $id, User $user, string $purpose) {
    return UserToken::where('purpose', $purpose)
      ->where('user_id', $user->id)
      ->where('id', $id)->first();
  }

  public function getUserTokenByTokenAndPurpose(string $token, string $purpose) {
    return UserToken::where('token', $token)
      ->where('purpose', $purpose)
      ->first();
  }

  public function getUserTokenByUserAndPurpose(User $user, string $purpose) {
    return UserToken::where('user_id', $user->id)
      ->where('purpose', $purpose)
      ->first();
  }

  public function getUserTokenByUserAndTokenAndPurpose(User $user, string $token, string $purpose) {
    return UserToken::where('user_id', $user->id)
      ->where('token', $token)
      ->where('purpose', $purpose)
      ->first();
  }

  public function getUserTokensByUserAndPurposeOrderedByDate(User $user, string $purpose) {
    return UserToken::where('user_id', $user->id)
      ->where('purpose', $purpose)
      ->orderBy('updated_at', 'ASC')
      ->get();
  }
}
