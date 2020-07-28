<?php

namespace App\Repositories\User\User;

use App\Models\User\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IUserRepository
{

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
   * @param string|null $memberNumber
   * @param string|null $internalComment
   * @param bool $informationDenied
   * @param string $bvMember
   * @param User|null $user
   * @return User|null
   * @throws Exception
   */
  public function createOrUpdateUser($title, $username, $firstname, $surname, $birthday, $joinDate, $streetname,
                                     $streetnumber, $zipcode, $location, $activated, $activity, $phoneNumbers,
                                     $emailAddresses, $memberNumber, $internalComment, $informationDenied = null,
                                     $bvMember = null, User $user = null);

  /**
   * @param User $user
   * @param string[] $emailAddresses
   * @return bool|null
   */
  public function updateUserEmailAddresses(User $user, $emailAddresses);

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
   * @return Collection<User>|null
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