<?php

namespace App\Models\Cinema;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use stdClass;

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
   * @return stdClass
   */
  public function getReturnable() {
    $returnableMovie = new stdClass();

    $returnableMovie->id = $this->id;
    $returnableMovie->name = $this->name;
    $returnableMovie->date = $this->date;
    $returnableMovie->trailer_link = $this->trailerLink;
    $returnableMovie->poster_link = $this->posterLink;
    $returnableMovie->booked_tickets = $this->bookedTickets;
    $returnableMovie->movie_year_id = $this->movie_year_id;
    $returnableMovie->created_at = $this->created_at;
    $returnableMovie->updated_at = $this->updated_at;

    $worker = $this->worker();
    $emergencyWorker = $this->emergencyWorker();

    if ($worker == null) {
      $returnableMovie->worker_id = null;
      $returnableMovie->worker_name = null;
    } else {
      $returnableMovie->worker_id = $worker->id;
      $returnableMovie->worker_name = $worker->firstname . ' ' . $worker->surname;
    }

    if ($emergencyWorker == null) {
      $returnableMovie->emergency_worker_id = null;
      $returnableMovie->emergency_worker_name = null;
    } else {
      $returnableMovie->emergency_worker_id = $emergencyWorker->id;
      $returnableMovie->emergency_worker_name = $emergencyWorker->firstname . ' ' . $emergencyWorker->surname;
    }

    return $returnableMovie;
  }

  /**
   * @return stdClass
   */
  public function getAdminReturnable() {
    $returnableMovie = $this->getReturnable();
    $bookings = array();
    foreach ($this->moviesBookings() as $moviesBooking) {
      $booking = new stdClass();
      $booking->user_id = $moviesBooking->user()->id;
      $booking->firstname = $moviesBooking->user()->firstname;
      $booking->surname = $moviesBooking->user()->surname;
      $booking->amount = $moviesBooking->amount;
      $bookings[] = $booking;
    }

    $usersNotBooked = DB::select("SELECT id, firstname, surname FROM users WHERE users.id 
                                                                     NOT IN (SELECT mb.user_id FROM movies_bookings mb 
                                                                     WHERE mb.movie_id = " . $this->id . ")");

    foreach ($usersNotBooked as $user) {
      $booking = new stdClass();
      $booking->user_id = $user->id;
      $booking->firstname = $user->firstname;
      $booking->surname = $user->surname;
      $booking->amount = 0;

      $bookings[] = $booking;
    }

    $returnableMovie->bookings = $bookings;
    return $returnableMovie;
  }
}
