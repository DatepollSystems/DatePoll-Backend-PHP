<?php

namespace App\Models\Groups;

use App\Models\Subgroups\Subgroup;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use stdClass;

/**
 * @property int $id
 * @property string $name
 * @property int $orderN
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property Subgroup[] $subgroups
 * @property UsersMemberOfGroups[] $usersMemberOfGroups
 */
class Group extends Model
{
  /**
   * @var array
   */
  protected $fillable = [
    'name',
    'orderN',
    'description',
    'created_at',
    'updated_at'];

  /**
   * @return Collection | Subgroup[] | null
   */
  public function subgroups() {
    return $this->hasMany('App\Models\Subgroups\Subgroup')
                ->get();
  }

  /**
   * @return Collection | Subgroup[]
   */
  public function getSubgroupsOrdered() {
    return $this->hasMany('App\Models\Subgroups\Subgroup')
                ->orderBy('orderN')
                ->get();
  }

  /**
   * @return Collection | UsersMemberOfGroups[] | null
   */
  public function usersMemberOfGroups() {
    return $this->hasMany('App\Models\Groups\UsersMemberOfGroups')
                ->get();
  }

  /**
   * @return stdClass[]
   */
  public function getUsersWithRolesOrderedBySurname() {
    $rUsers = [];
    $users = $this->usersMemberOfGroups();
    foreach ($users as $userS) {
      $user = new stdClass();
      $user->id = $userS->user_id;
      $user->firstname = $userS->user()->firstname;
      $user->surname = $userS->user()->surname;
      $user->role = $userS->role;

      $rUsers[] = $user;
    }
    usort($rUsers, function ($a, $b) {
      return strcmp($a->surname, $b->surname);
    });
    return $rUsers;
  }

  /**
   * @return User[] | null
   */
  public function getUsersOrderedBySurname() {
    $usersMemberOfGroups = $this->usersMemberOfGroups();
    $users = array();
    foreach ($usersMemberOfGroups as $usersMemberOfGroup) {
      $users[] = $usersMemberOfGroup->user();
    }
    usort($users, function ($a, $b) {
      return strcmp($a->surname, $b->surname);
    });
    return $users;
  }
}
