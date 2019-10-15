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
   * @param string $title
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
   * @param array $permissions
   * @param array $emailAddresses
   * @param User|null $user
   * @return User|null
   */
  public function createOrUpdateUser(string $title, string $username, string $firstname, string $surname, string $birthday, string $joinDate, string $streetname, string $streetnumber, int $zipcode, string $location, bool $activated, string $activity, array $phoneNumbers, array $permissions, array $emailAddresses, User $user = null);

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
}