<?php

namespace App\Repositories\User\UserToken;

use App\Models\User\User;
use App\Models\User\UserToken;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IUserTokenRepository
{

  /**
   * @param User $user
   * @param string $token
   * @param string $purpose
   * @param string $description
   * @return UserToken|null
   */
  public function createUserToken(User $user, string $token, string $purpose, $description = null);

  /**
   * @param UserToken $userToken
   * @return UserToken|null
   */
  public function deleteUserToken(UserToken $userToken);

  /**
   * @param int $length
   * @return string
   */
  public function generateUniqueRandomToken(int $length);

  /**
   * @param int $id
   * @param User $user
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByIdAndUserAndPurpose(int $id, User $user, string $purpose);

  /**
   * @param string $token
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByTokenAndPurpose(string $token, string $purpose);

  /**
   * @param User $user
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByUserAndPurpose(User $user, string $purpose);

  /**
   * @param User $user
   * @param string $token
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByUserAndTokenAndPurpose(User $user, string $token, string $purpose);

  /**
   * @param User $user
   * @param string $purpose
   * @return Collection<UserToken>|null
   */
  public function getUserTokensByUserAndPurposeOrderedByDate(User $user, string $purpose);
}