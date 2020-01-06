<?php

namespace App\Repositories\User\User;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;

interface IUserRepository
{

  /**
   * @return User[]|Collection
   */
  public function getAllUsers();

  /**
   * @param int $id
   * @return User|null
   */
  public function getUserById(int $id);

  /**
   * @param string $username
   * @return User|null
   */
  public function getUserByUsername(string $username);

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
   * @param User|null $user
   * @return User|null
   */
  public function createOrUpdateUser($title, $username, $firstname, $surname, $birthday, $joinDate, $streetname, $streetnumber, $zipcode, $location, $activated, $activity, $phoneNumbers, $emailAddresses, User $user = null);

  /**
   * @param array $permissions
   * @param User $user
   * @return bool
   */
  public function createOrUpdatePermissionsForUser($permissions, User $user);

  /**
   * @param User $user
   */
  public function activateUser(User $user);

  /**
   * @param User $user
   * @return bool|null
   */
  public function deleteUser(User $user);

  /**
   * @return array
   */
  public function exportAllUsers();

  /**
   * @return User[]|null
   */
  public function getAllNotActivatedUsers();

  /**
   * @param User $user
   * @param string $notHashedPassword
   * @return bool
   */
  public function changePasswordOfUser(User $user, string $notHashedPassword);

  /**
   * @param User $user
   * @param string $password
   * @return bool
   */
  public function checkPasswordOfUser(User $user, string $password);

  /**
   * @param User $user
   * @return array
   */
  public function getHomepageDataForUser(User $user);
}