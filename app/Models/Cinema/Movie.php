<?php

namespace App\Models\Cinema;

use App\Models\User\User;
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
    return $this->belongsTo('App\Models\User\User', 'emergency_worker_id')->first();
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
    return $this->belongsTo('App\Models\User\User', 'worker_id')->first();
  }

  /**
   * @return Collection
   */
  public function moviesBookings() {
    return $this->hasMany('App\Models\Cinema\MoviesBooking')->get();
  }

  /**
   * @return Movie
   */
  public function getReturnable() {
    $returnableMovie = $this;
    $workerID = $this->worker_id;
    $emergencyWorkerID = $this->emergency_worker_id;

    $worker = User::find($workerID);
    $emergencyWorker = User::find($emergencyWorkerID);

    if ($worker == null) {
      $returnableMovie->workerID = null;
      $returnableMovie->workerName = null;
    } else {
      $returnableMovie->workerID = $worker->id;
      $returnableMovie->workerName = $worker->getAttribute('firstname') . ' ' . $worker->getAttribute('surname');
    }

    if ($emergencyWorker == null) {
      $returnableMovie->emergencyWorkerID = null;
      $returnableMovie->emergencyWorkerName = null;
    } else {
      $returnableMovie->emergencyWorkerID = $emergencyWorker->id;
      $returnableMovie->emergencyWorkerName = $emergencyWorker->getAttribute('firstname') . ' ' . $emergencyWorker->getAttribute('surname');
    }

    return $returnableMovie;
  }
}
