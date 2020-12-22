<?php

namespace App\Models\User;

use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\Groups\Group;
use App\Models\Groups\UsersMemberOfGroups;
use App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument;
use App\Models\Subgroups\UsersMemberOfSubgroups;
use App\Utils\ArrayHelper;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

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
   * @return Collection | Movie[] | null
   */
  public function emergencyWorkerMovies() {
    return $this->hasMany('App\Models\Cinema\Movie', 'emergency_worker_id')
      ->get();
  }

  /**
   * @return Collection | Movie[] | null
   */
  public function workerMovies() {
    return $this->hasMany('App\Models\Cinema\Movie', 'worker_id')
      ->orderBy('date')
      ->get();
  }

  /**
   * @return Collection | MoviesBooking[] | null
   */
  public function moviesBookings() {
    return MoviesBooking::join('movies as m', 'm.id', '=', 'movies_bookings.movie_id')
      ->orderBy('m.date')
      ->select('movies_bookings.*')
      ->where('user_id', $this->id)
      ->get();
  }

  /**
   * @return Collection | UserCode[] | null
   */
  public function userCodes() {
    return $this->hasMany('App\Models\User\UserCode')
      ->get();
  }

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
   * @return array
   */
  public function getEmailAddresses(): array {
    return ArrayHelper::getPropertyArrayOfObjectArray($this->emailAddresses(), 'email');
  }

  /**
   * @return UsersMemberOfGroups[]
   */
  public function usersMemberOfGroups(): array {
    return $this->hasMany('App\Models\Groups\UsersMemberOfGroups')
      ->get()->all();
  }

  /**
   * @return Group[]
   */
  public function getGroups(): array {
    return array_map(function ($group) {
      return $group->group();
    }, $this->usersMemberOfGroups());
  }

  /**
   * @return Collection | UsersMemberOfSubgroups[] | null
   */
  public function usersMemberOfSubgroups() {
    return $this->hasMany('App\Models\Subgroups\UsersMemberOfSubgroups')
      ->get();
  }

  /**
   * @return UserHavePerformanceBadgeWithInstrument[]
   */
  public function getPerformanceBadges(): array {
    return $this->hasMany(UserHavePerformanceBadgeWithInstrument::class)
      ->get()->all();
  }

  /**
   * @return Collection | EventUserVotedForDecision[] | null
   */
  public function votedForDecisions() {
    return $this->hasMany('App\Models\Events\EventUserVotedForDecision')
      ->get();
  }

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
    return (UserPermission::where('user_id', '=', $this->id)->where('permission', '=', $permission)->orWhere('permission', '=', 'root.administration')->first() != null);
  }

  /**
   * @return string
   */
  public function getCompleteName(): string {
    return $this->firstname . ' ' . $this->surname;
  }

  /**
   * @return array
   */
  public function toArray(): array {
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
      'permissions' => ArrayHelper::getPropertyArrayOfObjectArray($this->getPermissions(), 'permission'),
      'performance_badges' => $this->getPerformanceBadges(),
    ];
  }
}
