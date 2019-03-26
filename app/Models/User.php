<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
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
 * @property UserPermission[] $userPermissions
 */
class User extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['email', 'force_password_change', 'password', 'title', 'firstname', 'surname', 'birthday', 'join_date', 'streetname', 'streetnumber', 'zipcode', 'location', 'created_at', 'updated_at', 'activated', 'activity'];

  public static function exists($userID) {
    $user = User::find($userID);
    return $user != null;
  }

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function emergencyWorkerMovies()
  {
    return $this->hasMany('App\Models\Cinema\Movie', 'emergency_worker_id')->get();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function workerMovies()
  {
    return $this->hasMany('App\Models\Cinema\Movie', 'worker_id')->get();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function moviesBookings()
  {
    return $this->hasMany('App\Models\Cinema\MoviesBooking')->get();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function userCodes()
  {
    return $this->hasMany('App\Models\UserCode')->get();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function telephoneNumbers() {
    return $this->hasMany('App\Models\UserTelephoneNumber')->get();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function usersMemberOfGroups() {
    return $this->hasMany('App\Models\Groups\UsersMemberOfGroups')->get();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function usersMemberOfSubgroups() {
    return $this->hasMany('App\Models\Subgroups\UsersMemberOfSubgroups')->get();
  }

  /**
   * @return \Illuminate\Database\Eloquent\Collection
   */
  public function permissions()
  {
    return $this->hasMany('App\Models\UserPermission')->get();
  }

  /**
   * @param $permission
   * @return bool
   */
  public function hasPermission($permission)
  {
    if($this->permissions()->where("permission", "=", $permission)->first()) {
      return true;
    }

    return false;
  }
}
