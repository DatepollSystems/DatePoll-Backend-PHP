<?php

namespace App\Models\User;

use App\Mail\ActivateUser;
use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use App\Models\Events\Event;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument;
use App\Models\UserCode;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
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
 * @property string $zipcode
 * @property string $location
 * @property boolean $activated
 * @property string $activity
 * @property string $created_at
 * @property string $updated_at
 * @property Movie[] emergencyWorkerMovies
 * @property Movie[] $movies
 * @property MoviesBooking[] $moviesBookings
 * @property UserCode[] $userCodes
 * @property UserHavePerformanceBadgeWithInstrument[] $userHavePerformanceBadgeWithInstrument
 * @property UserPermission[] $userPermissions
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
    'created_at',
    'updated_at',
    'activated',
    'activity'];

  public static function exists($userID) {
    $user = User::find($userID);
    return $user != null;
  }

  /**
   * @return Collection
   */
  public function emergencyWorkerMovies() {
    return $this->hasMany('App\Models\Cinema\Movie', 'emergency_worker_id')->get();
  }

  /**
   * @return Collection
   */
  public function workerMovies() {
    return $this->hasMany('App\Models\Cinema\Movie', 'worker_id')->orderBy('date')->get();
  }

  /**
   * @return Collection
   */
  public function moviesBookings() {
    return MoviesBooking::join('movies as m', 'm.id', '=', 'movies_bookings.movie_id')->orderBy('m.date')->select('movies_bookings.*')->where('user_id', $this->id)->get();
  }

  /**
   * @return Collection
   */
  public function userCodes() {
    return $this->hasMany('App\Models\User\UserCode')->get();
  }

  /**
   * @return Collection
   */
  public function telephoneNumbers() {
    return $this->hasMany('App\Models\User\UserTelephoneNumber')->get();
  }

  /**
   * @return Collection
   */
  public function emailAddresses() {
    return $this->hasMany('App\Models\User\UserEmailAddress')->get();
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
   * @return Collection
   */
  public function usersMemberOfGroups() {
    return $this->hasMany('App\Models\Groups\UsersMemberOfGroups')->get();
  }

  /**
   * @return Collection
   */
  public function usersMemberOfSubgroups() {
    return $this->hasMany('App\Models\Subgroups\UsersMemberOfSubgroups')->get();
  }

  /**
   * @return Collection
   */
  public function performanceBadges() {
    return $this->hasMany('App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument')->get();
  }

  /**
   * @return Collection
   */
  public function votedForDecisions() {
    return $this->hasMany('App\Models\Events\EventUserVotedForDecisions')->get();
  }

  /**
   * @return Collection
   */
  public function permissions() {
    return $this->hasMany('App\Models\User\UserPermission')->get();
  }

  /**
   * @param $permission
   * @return bool
   */
  public function hasPermission($permission) {
    if ($this->permissions()->where('permission', '=', 'root.administration')->first() != null) {
      return true;
    }

    if ($this->permissions()->where("permission", "=", $permission)->first() != null) {
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
    $returnableUser->force_password_change = $this->force_password_change;
    $returnableUser->phoneNumbers = $this->telephoneNumbers();
    $returnableUser->emailAddresses = $this->getEmailAddresses();

    $permissions = array();
    if($this->permissions() != null) {
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
      $performanceBadgeToReturn->performanceBadge_id = $performanceBadgeWithInstrument->performance_badge_id;
      $performanceBadgeToReturn->instrument_id = $performanceBadgeWithInstrument->instrument_id;
      $performanceBadgeToReturn->grade = $performanceBadgeWithInstrument->grade;
      $performanceBadgeToReturn->note = $performanceBadgeWithInstrument->note;
      if($performanceBadgeWithInstrument->date != '1970-01-01') {
        $performanceBadgeToReturn->date = $performanceBadgeWithInstrument->date;
      } else {
        $performanceBadgeToReturn->date = null;
      }
      $performanceBadgeToReturn->performanceBadge_name = $performanceBadgeWithInstrument->performanceBadge()->name;
      $performanceBadgeToReturn->instrument_name = $performanceBadgeWithInstrument->instrument()->name;

      $performanceBadgesToReturn[] = $performanceBadgeToReturn;
    }

    $returnableUser->performanceBadges = $performanceBadgesToReturn;

    return $returnableUser;
  }

  /**
   * Activates a user
   */
  public function activate() {
    $randomPassword = UserCode::generateCode();
    $this->password = app('hash')->make($randomPassword . $this->id);;
    $this->force_password_change = true;
    $this->save();

    Mail::bcc($this->getEmailAddresses())->send(new ActivateUser($this->firstname . " " . $this->surname, $this->username, $randomPassword));
  }

  /**
   * @return array
   */
  public function getOpenEvents() {
    $events = array();
    $allEvents = Event::orderBy('startDate')->get();
    foreach ($allEvents as $event) {
      if ((time() - (60 * 60 * 24)) < strtotime($event->startDate)) {

        $in = false;

        if ($event->forEveryone) {
          $in = true;
        } else {

          foreach ($event->eventsForGroups() as $eventForGroup) {
            foreach ($eventForGroup->group()->usersMemberOfGroups() as $userMemberOfGroup) {
              if ($userMemberOfGroup->user_id == $this->id) {
                $in = true;
                break;
              }
            }
          }

          if (!$in) {
            foreach ($event->eventsForSubgroups() as $eventForSubgroup) {
              foreach ($eventForSubgroup->subgroup()->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
                if ($userMemberOfSubgroup->user_id == $this->id) {
                  $in = true;
                  break;
                }
              }
            }
          }
        }

        if ($in) {
          $eventUserVotedFor = EventUserVotedForDecision::where('event_id', $event->id)->where('user_id', $this->id)->first();
          $alreadyVoted = ($eventUserVotedFor != null);

          $eventToReturn = $event->getReturnable();
          $eventToReturn->alreadyVoted = $alreadyVoted;
          $events[] = $eventToReturn;
        }
      }
    }

    return $events;
  }
}
