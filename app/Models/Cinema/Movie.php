<?php

namespace App\Models\Cinema;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $movie_year_id
 * @property int $worker_id
 * @property int $emergency_worker_id
 * @property string $name
 * @property string $date
 * @property string $trailerLink
 * @property string $posterLink
 * @property int $bookedTickets
 * @property string $created_at
 * @property string $updated_at
 * @property User $emergencyWorker
 * @property MovieYear $movieYear
 * @property User $worker
 * @property MoviesBooking[] $moviesBookings
 */
class Movie extends Model
{
  /**
   * @var array
   */
  protected $fillable = ['movie_year_id', 'worker_id', 'emergency_worker_id', 'name', 'date', 'trailerLink', 'posterLink', 'bookedTickets', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo
   */
  public function emergencyWorker() {
    return $this->belongsTo('App\Models\User', 'emergency_worker_id')->first();
  }

  /**
   * @return BelongsTo
   */
  public function movieYear() {
    return $this->belongsTo('App\Models\Cinema\MovieYear')->first();
  }

  /**
   * @return BelongsTo
   */
  public function worker() {
    return $this->belongsTo('App\Models\User', 'worker_id')->first();
  }

  /**
   * @return Collection
   */
  public function moviesBookings() {
    return $this->hasMany('App\Models\Cinema\MoviesBooking')->get();
  }
}
