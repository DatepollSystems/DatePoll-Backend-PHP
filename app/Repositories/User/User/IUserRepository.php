<?php

namespace App\Repositories\User\User;

use App\Models\User\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IUserRepository {

  /**
   * @return User[]|Collection
   */
  public function getAllUsers();

  /**
   * @return User[]|Collection
   */
  public function getAllUsersOrderedBySurname();

  /**
   * @param int $id
   * @return User|null
   */
  public function getUserById(int $id): ?User;

  /**
   * @param string $username
   * @return User|null
   */
  public function getUserByUsername(string $username): ?User;

  /**
   * @param string|null $title
   * @param string $username
   * @param string $firstname
   * @param string $surname
   * @param string $birthday
   * @param string $joinDate
   * @param string $streetname
   * @param string $streetnumber
   * @param int $zipcode
   * @param string $location
   * @param bool $activated
   * @param string $activity
   * @param array $phoneNumbers
   * @param string[] $emailAddresses
   * @param string|null $memberNumber
   * @param string|null $internalComment
   * @param bool $informationDenied
   * @param string|null $bvMember
   * @param int $editorId
   * @param User|null $user
   * @return User|null
   * @throws Exception
   */
  public function createOrUpdateUser(
    ?string $title,
    string $username,
    string $firstname,
    string $surname,
    string $birthday,
    string $joinDate,
    string $streetname,
    string $streetnumber,
    int $zipcode,
    string $location,
    bool $activated,
    string $activity,
    array $phoneNumbers,
    array $emailAddresses,
    ?string $memberNumber,
    ?string $internalComment,
    ?bool $informationDenied,
    ?string $bvMember,
    int $editorId,
    User $user = null
  ): ?User;

  /**
   * @param User $user
   * @param string[] $emailAddresses
   * @param int $editorId
   * @return bool
   * @throws Exception
   */
  public function updateUserEmailAddresses(User $user, array $emailAddresses, int $editorId): bool;

  /**
   * @param string[]|array $permissions
   * @param User $user
   * @return bool
   */
  public function createOrUpdatePermissionsForUser(array $permissions, User $user): bool;

  /**
   * @param User $user
   */
  public function activateUser(User $user): void;

  /**
   * @return array
   */
  public function exportAllUsers(): array;

  /**
   * @return User[]
   */
  public function getAllNotActivatedUsers(): array;

  /**
   * @param User $user
   * @param string $password
   * @return bool
   */
  public function changePasswordOfUser(User $user, string $password): bool;

  /**
   * @param User $user
   * @param string $password
   * @return bool
   */
  public function checkPasswordOfUser(User $user, string $password): bool;
}
