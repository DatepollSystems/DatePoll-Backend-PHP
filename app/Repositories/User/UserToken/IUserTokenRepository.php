<?php

namespace App\Repositories\User\UserToken;

use App\Models\User\User;
use App\Models\User\UserToken;

interface IUserTokenRepository {

  /**
   * @param int $userId
   * @param string $token
   * @param string $purpose
   * @param string|null $description
   * @return UserToken|null
   */
  public function createUserToken(int $userId, string $token, string $purpose, ?string $description = null): ?UserToken;

  /**
   * @param UserToken $userToken
   * @return UserToken|null
   */
  public function deleteUserToken(UserToken $userToken): ?UserToken;

  /**
   * @param int $length
   * @return string
   */
  public function generateUniqueRandomToken(int $length): string;

  /**
   * @param int $id
   * @param User $user
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByIdAndUserAndPurpose(int $id, User $user, string $purpose): ?UserToken;

  /**
   * @param string $token
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByTokenAndPurpose(string $token, string $purpose): ?UserToken;

  /**
   * @param int $userId
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByUserAndPurpose(int $userId, string $purpose): ?UserToken;

  /**
   * @param User $user
   * @param string $token
   * @param string $purpose
   * @return UserToken|null
   */
  public function getUserTokenByUserAndTokenAndPurpose(User $user, string $token, string $purpose): ?UserToken;

  /**
   * @param User $user
   * @param string $purpose
   * @return UserToken[]|null
   */
  public function getUserTokensByUserAndPurposeOrderedByDate(User $user, string $purpose): ?array;
}
