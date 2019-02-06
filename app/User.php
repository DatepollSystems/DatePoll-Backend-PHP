<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property boolean $email_verified
 * @property string $password
 * @property string $rank
 * @property string $firstname
 * @property string $surname
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Movie[] $movies
 * @property Movie[] $s
 * @property MoviesBooking[] $moviesBookings
 */
class User extends Model
{
  /**
   * @var array
   */
  protected $fillable = [
    'email',
    'email_verified',
    'force_password_change',
    'password',
    'rank',
    'title',
    'firstname',
    'surname',
    'birthday',
    'streetname',
    'streetnumber',
    'zipcode',
    'location',
    'remember_token',
    'created_at',
    'updated_at'];

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function moviesAsEmergencyWorker()
  {
    return $this->hasMany('App\Movie', 'emergency_worker_id');
  }

  /**
   * @return \Illuminate\Database\Eloquent\Relations\HasMany
   */
  public function moviesAsWorker()
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
  public function codes()
  {
    return $this->hasMany('App\UserCode');
  }
}
