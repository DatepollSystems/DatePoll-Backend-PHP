<?php

namespace App;

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
 * @property string $remember_token
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
  protected $fillable = ['email', 'email_verified', 'force_password_change', 'password', 'title', 'firstname', 'surname', 'birthday', 'join_date', 'streetname', 'streetnumber', 'zipcode', 'location', 'remember_token', 'created_at', 'updated_at', 'activated', 'activity'];

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function emergencyWorkerMovies()
  {
    return $this->hasMany('App\Movie', 'emergency_worker_id');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function movies()
  {
    return $this->hasMany('App\Movie', 'worker_id');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function moviesBookings()
  {
    return $this->hasMany('App\MoviesBooking');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function userCodes()
  {
    return $this->hasMany('App\UserCode');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function telephoneNumbers() {
    return $this->hasMany('App\UserTelephoneNumber');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function userPermissions()
  {
    return $this->hasMany('App\UserPermission');
  }

  /**
   * @param $permission
   * @return bool
   */
  public function hasPermission($permission)
  {
    if($this->userPermissions()->where("permission", "=", $permission)->first()) {
      return true;
    }

    return false;
  }
}
