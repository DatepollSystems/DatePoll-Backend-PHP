<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $email
 * @property boolean $email_verified
 * @property string $email_verify_token
 * @property string $password
 * @property string $rank
 * @property string $firstname
 * @property string $surname
 * @property string $remember_token
 * @property string $created_at
 * @property string $updated_at
 * @property Movie[] $movies
 * @property Movie[] $movies
 * @property MoviesBooking[] $moviesBookings
 */
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;

class User extends Model // implements AuthenticatableContract, AuthorizableContract
{
//  use Authenticatable, Authorizable;
    /**
     * @var array
     */
    protected $fillable = ['email', 'email_verified', 'email_verify_token', 'password', 'rank', 'firstname', 'surname', 'remember_token', 'created_at', 'updated_at'];

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
}
