<?php

namespace App\Models\Subgroups;

use App\Models\Broadcasts\BroadcastForSubgroup;
use App\Models\Events\EventForSubgroup;
use App\Models\Groups\Group;
use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
  protected $with = ['group'];

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
   * @return BelongsTo
   */
  public function group(): BelongsTo {
    return $this->belongsTo(Group::class);
  }

  /**
   * @return BelongsTo|Group
   */
  public function getGroup(): BelongsTo|Group {
    return $this->group()->first();
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
    foreach ($this->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
      $user = [];
      $user['id'] = $userMemberOfSubgroup->user_id;
      $user['firstname'] = $userMemberOfSubgroup->user->firstname;
      $user['surname'] = $userMemberOfSubgroup->user->surname;
      $user['role'] = $userMemberOfSubgroup->role;

      $rUsers[] = $user;
    }
    usort($rUsers, static function ($a, $b) {
      return strcmp($a['surname'], $b['surname']);
    });

    return $rUsers;
  }

  /**
   * @return User[]
   */
  public function getUsersOrderedBySurname(): array {
    $users = array_map(static function ($userMemberOfSubgroups) {
      return $userMemberOfSubgroups->user;
    }, $this->usersMemberOfSubgroups());

    usort($users, static function ($a, $b) {
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
