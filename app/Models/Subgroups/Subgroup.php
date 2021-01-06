<?php

namespace App\Models\Subgroups;

use App\Models\Broadcasts\BroadcastForSubgroup;
use App\Models\Events\EventForSubgroup;
use App\Models\Groups\Group;
use App\Models\User\User;
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
class Subgroup extends Model {
  /**
   * @var array
   */
  protected $fillable = [
    'group_id',
    'name',
    'orderN',
    'description',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | Group
   */
  public function group(): BelongsTo|Group {
    return $this->belongsTo(Group::class)
      ->first();
  }

  /**
   * @return UsersMemberOfSubgroups[]
   */
  public function usersMemberOfSubgroups(): array {
    return $this->hasMany(UsersMemberOfSubgroups::class)
      ->get()->all();
  }

  /**
   * @return array
   */
  public function getUsersWithRolesOrderedBySurname(): array {
    $rUsers = [];
    foreach ($this->usersMemberOfSubgroups() as $userS) {
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
   * @return User[]
   */
  public function getUsersOrderedBySurname(): array {
    $users = [];
    foreach ( $this->usersMemberOfSubgroups() as $usersMemberOfSubgroup) {
      $users[] = $usersMemberOfSubgroup->user();
    }
    usort($users, function ($a, $b) {
      return strcmp($a->surname, $b->surname);
    });

    return $users;
  }

  /**
   * @return EventForSubgroup[]
   */
  public function eventsForSubgroups(): array {
    return $this->hasMany(EventForSubgroup::class)
      ->get()->all();
  }

  /**
   * @return BroadcastForSubgroup[]
   */
  public function broadcastsForSubgroups(): array {
    return $this->hasMany(BroadcastForSubgroup::class)
      ->get()->all();
  }
}
