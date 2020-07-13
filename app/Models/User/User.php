<?php

namespace App\Models\User;

use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\Groups\UsersMemberOfGroups;
use App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument;
use App\Models\Subgroups\UsersMemberOfSubgroups;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use stdClass;

/**
 * @property int $id
 * @property string $username
 * @property boolean $email_verified
 * @property boolean $force_password_change
 * @property string $password
 * @property string $rank
 * @property string $title
 * @property string $firstname
 * @property string $surname
 * @property string $birthday
 * @property string $join_date
 * @property string $streetname
 * @property string $streetnumber
 * @property int $member_number
 * @property string $zipcode
 * @property string $location
 * @property boolean $activated
 * @property string $internal_comment
 * @property boolean $information_denied
 * @property string $activity
 * @property boolean $bv_member
 * @property string $created_at
 * @property string $updated_at
 */
class User extends Model
{
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
    'activity'];

  public static function exists($userID) {
    $user = User::find($userID);
    return $user != null;
  }

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
   * @return Collection | UserTelephoneNumber[] | null
   */
  public function telephoneNumbers() {
    return $this->hasMany('App\Models\User\UserTelephoneNumber')
                ->get();
  }

  /**
   * @return Collection | UserEmailAddress[] | null
   */
  public function emailAddresses() {
    return $this->hasMany('App\Models\User\UserEmailAddress')
                ->get();
  }

  /**
   * @return bool
   */
  public function hasEmailAddresses() {
    return (sizeof($this->emailAddresses()) > 0);
  }

  /**
   * @return array
   */
  public function getEmailAddresses() {
    $emailAddresses = [];
    foreach ($this->emailAddresses() as $emailAddressObject) {
      $emailAddresses[] = $emailAddressObject['email'];
    }
    return $emailAddresses;
  }

  /**
   * @return Collection | UsersMemberOfGroups[] | null
   */
  public function usersMemberOfGroups() {
    return $this->hasMany('App\Models\Groups\UsersMemberOfGroups')
                ->get();
  }

  /**
   * @return Collection | UsersMemberOfSubgroups[] | null
   */
  public function usersMemberOfSubgroups() {
    return $this->hasMany('App\Models\Subgroups\UsersMemberOfSubgroups')
                ->get();
  }

  /**
   * @return Collection | UserHavePerformanceBadgeWithInstrument[] | null
   */
  public function performanceBadges() {
    return $this->hasMany('App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument')
                ->get();
  }

  /**
   * @return Collection | EventUserVotedForDecision[] | null
   */
  public function votedForDecisions() {
    return $this->hasMany('App\Models\Events\EventUserVotedForDecisions')
                ->get();
  }

  /**
   * @return Collection | UserPermission[] | null
   */
  public function permissions() {
    return $this->hasMany('App\Models\User\UserPermission')
                ->get();
  }

  /**
   * @param string $permission
   * @return bool
   */
  public function hasPermission($permission) {
    if ($this->permissions()
             ->where('permission', '=', 'root.administration')
             ->first() != null) {
      return true;
    }

    if ($this->permissions()
             ->where("permission", "=", $permission)
             ->first() != null) {
      return true;
    }

    return false;
  }

  /**
   * Returns a DTO object for the user
   *
   * @return stdClass
   */
  public function getReturnable() {
    $returnableUser = new stdClass();

    $returnableUser->id = $this->id;
    $returnableUser->title = $this->title;
    $returnableUser->firstname = $this->firstname;
    $returnableUser->surname = $this->surname;
    $returnableUser->username = $this->username;
    $returnableUser->birthday = $this->birthday;
    $returnableUser->join_date = $this->join_date;
    $returnableUser->streetname = $this->streetname;
    $returnableUser->streetnumber = $this->streetnumber;
    $returnableUser->zipcode = $this->zipcode;
    $returnableUser->location = $this->location;
    $returnableUser->activated = $this->activated;
    $returnableUser->activity = $this->activity;
    $returnableUser->member_number = $this->member_number;
    $returnableUser->internal_comment = $this->internal_comment;
    $returnableUser->information_denied = $this->information_denied;
    $returnableUser->bv_member = $this->bv_member;
    $returnableUser->force_password_change = $this->force_password_change;
    $returnableUser->phone_numbers = $this->telephoneNumbers();
    $returnableUser->email_addresses = $this->getEmailAddresses();

    $permissions = array();
    if ($this->permissions() != null) {
      foreach ($this->permissions() as $permission) {
        $permissions[] = $permission->permission;
      }
    }

    $returnableUser->permissions = $permissions;

    $performanceBadgesToReturn = [];

    $userHasPerformanceBadgesWithInstruments = $this->performanceBadges();
    foreach ($userHasPerformanceBadgesWithInstruments as $performanceBadgeWithInstrument) {
      $performanceBadgeToReturn = new stdClass();
      $performanceBadgeToReturn->id = $performanceBadgeWithInstrument->id;
      $performanceBadgeToReturn->performance_badge_id = $performanceBadgeWithInstrument->performance_badge_id;
      $performanceBadgeToReturn->instrument_id = $performanceBadgeWithInstrument->instrument_id;
      $performanceBadgeToReturn->grade = $performanceBadgeWithInstrument->grade;
      $performanceBadgeToReturn->note = $performanceBadgeWithInstrument->note;
      if ($performanceBadgeWithInstrument->date != '1970-01-01') {
        $performanceBadgeToReturn->date = $performanceBadgeWithInstrument->date;
      } else {
        $performanceBadgeToReturn->date = null;
      }
      $performanceBadgeToReturn->performance_badge_name = $performanceBadgeWithInstrument->performanceBadge()->name;
      $performanceBadgeToReturn->instrument_name = $performanceBadgeWithInstrument->instrument()->name;

      $performanceBadgesToReturn[] = $performanceBadgeToReturn;
    }

    $returnableUser->performance_badges = $performanceBadgesToReturn;

    return $returnableUser;
  }
}
