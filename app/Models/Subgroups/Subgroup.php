<?php

namespace App\Models\Subgroups;

use App\Models\Events\EventForSubgroup;
use App\Models\Groups\Group;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use stdClass;

/**
 * @property int $id
 * @property int $group_id
 * @property int $orderN
 * @property string $name
 * @property string $description
 * @property string $created_at
 * @property string $updated_at
 * @property Group $group
 * @property UsersMemberOfSubgroups[] $usersMemberOfSubgroups
 */
class Subgroup extends Model
{
  /**
   * @var array
   */
  protected $fillable = [
    'group_id',
    'name',
    'orderN',
    'description',
    'created_at',
    'updated_at'];

  /**
   * @return BelongsTo | Group
   */
  public function group() {
    return $this->belongsTo('App\Models\Groups\Group')
                ->first();
  }

  /**
   * @return Collection | UsersMemberOfSubgroups[] | null
   */
  public function usersMemberOfSubgroups() {
    return $this->hasMany('App\Models\Subgroups\UsersMemberOfSubgroups')
                ->get();
  }

  /**
   * @return stdClass[]
   */
  public function getUsersWithRolesOrderedBySurname() {
    $rUsers = [];
    $users = $this->usersMemberOfSubgroups();
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
    $usersMemberOfSubgroups = $this->usersMemberOfSubgroups();
    $users = array();
    foreach ($usersMemberOfSubgroups as $usersMemberOfSubgroup) {
      $users[] = $usersMemberOfSubgroup->user();
    }
    usort($users, function ($a, $b) {
      return strcmp($a->surname, $b->surname);
    });
    return $users;
  }

  /**
   * @return Collection | EventForSubgroup[] | null
   */
  public function eventsForSubgroups() {
    return $this->hasMany(EventForSubgroup::class)
      ->get();
  }
}
