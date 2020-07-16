<?php

namespace App\Models\Groups;

use App\Models\Subgroups\Subgroup;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
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
  protected $fillable = ['name', 'description', 'created_at', 'updated_at'];

  /**
   * @return Collection | Subgroup[] | null
   */
  public function subgroups() {
    return $this->hasMany('App\Models\Subgroups\Subgroup')->get();
  }

  /**
   * @return Collection | Subgroup[]
   */
  public function getSubgroupsOrderedByName() {
    return $this->hasMany('App\Models\Subgroups\Subgroup')->orderBy('name')->get();
  }

  /**
   * @return Collection | UsersMemberOfGroups[] | null
   */
  public function usersMemberOfGroups() {
    return $this->hasMany('App\Models\Groups\UsersMemberOfGroups')->get();
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
