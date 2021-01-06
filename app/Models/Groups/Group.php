<?php

namespace App\Models\Groups;

use App\Models\Broadcasts\BroadcastForGroup;
use App\Models\Events\EventForGroup;
use App\Models\SeatReservation\PlaceReservationNotifyGroup;
use App\Models\Subgroups\Subgroup;
use App\Models\User\User;
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
class Group extends Model {
  /**
   * @var array
   */
  protected $fillable = [
    'name',
    'orderN',
    'description',
    'created_at',
    'updated_at', ];

  /**
   * @return Subgroup[]
   */
  public function subgroups(): array {
    return $this->hasMany('App\Models\Subgroups\Subgroup')
      ->get()->all();
  }

  /**
   * @return Subgroup[]
   */
  public function getSubgroupsOrdered(): array {
    return $this->hasMany('App\Models\Subgroups\Subgroup')
      ->orderBy('orderN')
      ->get()->all();
  }

  /**
   * @return UsersMemberOfGroups[]
   */
  public function usersMemberOfGroups(): array {
    return $this->hasMany('App\Models\Groups\UsersMemberOfGroups')
      ->get()->all();
  }

  /**
   * @return stdClass[]
   */
  public function getUsersWithRolesOrderedBySurname(): array {
    $rUsers = [];
    foreach ($this->usersMemberOfGroups() as $userS) {
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
    foreach ($this->usersMemberOfGroups() as $usersMemberOfGroup) {
      $users[] = $usersMemberOfGroup->user();
    }
    usort($users, function ($a, $b) {
      return strcmp($a->surname, $b->surname);
    });

    return $users;
  }

  /**
   * @return EventForGroup[]
   */
  public function eventsForGroups(): array {
    return $this->hasMany(EventForGroup::class)
      ->get()->all();
  }

  /**
   * @return BroadcastForGroup[]
   */
  public function broadcastsForGroups(): array {
    return $this->hasMany(BroadcastForGroup::class)
      ->get()->all();
  }

  /**
   * @return bool
   */
  public function hasToBeNotifiedOnPlaceReservation(): bool {
    return $this->hasMany(PlaceReservationNotifyGroup::class)
      ->get()->count() > 0;
  }
}
