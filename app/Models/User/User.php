<?php

namespace App\Models\User;

use App\Models\Broadcasts\Broadcast;
use App\Models\Broadcasts\BroadcastUserInfo;
use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\Groups\Group;
use App\Models\Groups\UsersMemberOfGroups;
use App\Models\PerformanceBadge\UserHasBadge;
use App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument;
use App\Models\SeatReservation\PlaceReservation;
use App\Models\Subgroups\UsersMemberOfSubgroups;
use App\Permissions;
use App\Utils\ArrayHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int $id
 * @property string $username
 * @property boolean $email_verified
 * @property boolean $force_password_change
 * @property string $password
 * @property string $title
 * @property string $firstname
 * @property string $surname
 * @property string $birthday
 * @property string $join_date
 * @property string $streetname
 * @property string $streetnumber
 * @property string $member_number
 * @property int $zipcode
 * @property string $location
 * @property boolean $activated
 * @property string $internal_comment
 * @property boolean $information_denied
 * @property string $activity
 * @property string $bv_member
 * @property string $created_at
 * @property string $updated_at
 */
class User extends Model {
  /**
   * @var array
   */
  protected $fillable = [
    'username',
    'force_password_change',
    'password',
    'title',
    'firstname',
    'surname',
    'birthday',
    'join_date',
    'streetname',
    'streetnumber',
    'zipcode',
    'location',
    'member_number',
    'internal_comment',
    'information_denied',
    'bv_member',
    'created_at',
    'updated_at',
    'activated',
    'activity',];

  /**
   * @return string
   */
  public function getCompleteName(): string {
    return $this->firstname . ' ' . $this->surname;
  }

  /**
   * @return UserCode[]
   */
  public function userCodes(): array {
    return $this->hasMany(UserCode::class)
      ->get()->all();
  }

  // ------------------------------------ Properties ------------------------------------

  /**
   * @return UserTelephoneNumber[]
   */
  public function telephoneNumbers(): array {
    return $this->hasMany(UserTelephoneNumber::class)
      ->get()->all();
  }

  /**
   * @return UserEmailAddress[]
   */
  public function emailAddresses(): array {
    return $this->hasMany(UserEmailAddress::class)
      ->get()->all();
  }

  /**
   * @return bool
   */
  public function hasEmailAddresses(): bool {
    return (ArrayHelper::getCount($this->emailAddresses()) > 0);
  }

  /**
   * @return string[]
   */
  public function getEmailAddresses(): array {
    return ArrayHelper::getPropertyArrayOfObjectArray($this->emailAddresses(), 'email');
  }

  // ------------------------------------ Groups and subgroups ------------------------------------

  /**
   * @return UsersMemberOfGroups[]
   */
  public function usersMemberOfGroups(): array {
    return $this->hasMany(UsersMemberOfGroups::class)
      ->get()->all();
  }

  /**
   * @return Group[]
   */
  public function getGroups(): array {
    return ArrayHelper::getPropertyArrayOfObjectArray($this->usersMemberOfGroups(), 'group');
  }

  /**
   * @return UsersMemberOfSubgroups[]
   */
  public function usersMemberOfSubgroups(): array {
    return $this->hasMany(UsersMemberOfSubgroups::class)
      ->get()->all();
  }

  // ------------------------------------ Badges ------------------------------------

  /**
   * @return UserHavePerformanceBadgeWithInstrument[]
   */
  public function getPerformanceBadges(): array {
    return $this->hasMany(UserHavePerformanceBadgeWithInstrument::class)
      ->get()->all();
  }

  /**
   * @return UserHasBadge[]
   */
  public function getBadges(): array {
    return $this->hasMany(UserHasBadge::class)->get()->all();
  }

  // ------------------------------------ Permissions ------------------------------------

  /**
   * @return UserPermission[]
   */
  public function getPermissions(): array {
    return $this->hasMany(UserPermission::class)
      ->get()->all();
  }

  /**
   * @param string $permission
   * @return bool
   */
  public function hasPermission(string $permission): bool {
    if (DB::table('user_permissions')->where('permission', '=', Permissions::$ROOT_ADMINISTRATION)->where(
      'user_id',
      '=',
      $this->id
    )->count() > 0 || DB::table('user_permissions')->where(
      'permission',
      '=',
      $permission
    )->where('user_id', '=', $this->id)->count() > 0) {
      return true;
    }

    foreach ($this->getGroups() as $group) {
      if (DB::table('group_permissions')->where('permission', '=', Permissions::$ROOT_ADMINISTRATION)->where(
        'group_id',
        '=',
        $group->id
      )->count() > 0 || DB::table('group_permissions')->where(
        'permission',
        '=',
        $permission
      )->where('group_id', '=', $group->id)->count() > 0) {
        return true;
      }
    }

    return false;
  }

  // ------------------------------------ Cinema ------------------------------------

  /**
   * @return Movie[]
   */
  public function emergencyWorkerMovies(): array {
    return $this->hasMany(Movie::class, 'emergency_worker_id')
      ->get()->all();
  }

  /**
   * @return Movie[]
   */
  public function workerMovies(): array {
    return $this->hasMany(Movie::class, 'worker_id')
      ->orderBy('date')
      ->get()->all();
  }

  /**
   * @return MoviesBooking[]
   */
  public function moviesBookings(): array {
    return MoviesBooking::join('movies as m', 'm.id', '=', 'movies_bookings.movie_id')
      ->orderBy('m.date')
      ->select('movies_bookings.*')
      ->where('user_id', $this->id)
      ->get()->all();
  }

  // ------------------------------------ Events ------------------------------------

  /**
   * @return EventUserVotedForDecision[]
   */
  public function votedForDecisions(): array {
    return $this->hasMany(EventUserVotedForDecision::class)
      ->get()->all();
  }

  // ------------------------------------ Broadcasts ------------------------------------

  /**
   * @return Broadcast[]
   */
  public function writerOfBroadcasts(): array {
    return $this->hasMany(Broadcast::class, 'writer_user_id')
      ->get()->all();
  }

  /**
   * @return BroadcastUserInfo[]
   */
  public function broadcastUserInfos(): array {
    return $this->hasMany(BroadcastUserInfo::class, 'user_id')
      ->get()->all();
  }

  // ------------------------------------ Place reservations ------------------------------------

  /**
   * @return PlaceReservation[]
   */
  public function approverOfPlaceReservations(): array {
    return $this->hasMany(PlaceReservation::class, 'approver_id')
      ->get()->all();
  }

  /**
   * @return PlaceReservation[]
   */
  public function requestedPlaceReservations(): array {
    return $this->hasMany(PlaceReservation::class, 'user_id')
      ->get()->all();
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $permissions = [];
    foreach ($this->getGroups() as $group) {
      foreach ($group->toArray()['permissions'] as $permission) {
        $permissions[] = $permission;
      }
    }
    foreach ($this->getPermissions() as $permission) {
      $permissions[] = $permission->permission;
    }

    return [
      'id' => $this->id,
      'title' => $this->title,
      'firstname' => $this->firstname,
      'surname' => $this->surname,
      'username' => $this->username,
      'birthday' => $this->birthday,
      'join_date' => $this->join_date,
      'streetname' => $this->streetname,
      'streetnumber' => $this->streetnumber,
      'zipcode' => $this->zipcode,
      'location' => $this->location,
      'activated' => $this->activated,
      'activity' => $this->activity,
      'member_number' => $this->member_number,
      'internal_comment' => $this->internal_comment,
      'information_denied' => $this->information_denied,
      'bv_member' => $this->bv_member,
      'force_password_change' => $this->force_password_change,
      'phone_numbers' => $this->telephoneNumbers(),
      'email_addresses' => $this->getEmailAddresses(),
      'permissions' => $permissions,
      'performance_badges' => $this->getPerformanceBadges(),
    ];
  }
}
