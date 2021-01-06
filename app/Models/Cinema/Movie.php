<?php

namespace App\Models\Cinema;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\ArrayShape;
use stdClass;

/**
 * @property int $id
 * @property int $movie_year_id
 * @property int|null $worker_id
 * @property int|null $emergency_worker_id
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
class Movie extends Model {
  /**
   * @var array
   */
  protected $fillable = ['movie_year_id', 'worker_id', 'emergency_worker_id', 'name', 'date', 'trailerLink', 'posterLink', 'bookedTickets', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo | User | null
   */
  public function emergencyWorker(): BelongsTo|User|null {
    return $this->belongsTo(User::class, 'emergency_worker_id')->first();
  }

  /**
   * @return BelongsTo | MovieYear | null
   */
  public function movieYear(): BelongsTo|MovieYear|null {
    return $this->belongsTo(MovieYear::class)->first();
  }

  /**
   * @return BelongsTo  | User | null
   */
  public function worker(): BelongsTo|User|null {
    return $this->belongsTo(User::class, 'worker_id')->first();
  }

  /**
   * @return MoviesBooking[]
   */
  public function moviesBookings(): array {
    return $this->hasMany('App\Models\Cinema\MoviesBooking')->get()->all();
  }

  /**
   * @return array
   */
  #[ArrayShape(["id" => "int", 'name' => "string", 'date' => "string", 'trailer_link' => "string", 'poster_link' => "string", 'booked_tickets' => "int", 'movie_year_id' => "int", 'created_at' => "string", 'updated_at' => "string"])]
  public function toArray(): array {
    $returnable = [
      "id" => $this->id,
      'name' => $this->name,
      'date' => $this->date,
      'trailer_link' => $this->trailerLink,
      'poster_link' => $this->posterLink,
      'booked_tickets' => $this->bookedTickets,
      'movie_year_id' => $this->movie_year_id,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];

    $worker = $this->worker();
    $emergencyWorker = $this->emergencyWorker();

    if ($worker == null) {
      $returnable->worker_id = null;
      $returnable->worker_name = null;
    } else {
      $returnable->worker_id = $worker->id;
      $returnable->worker_name = $worker->getCompleteName();
    }

    if ($emergencyWorker == null) {
      $returnable->emergency_worker_id = null;
      $returnable->emergency_worker_name = null;
    } else {
      $returnable->emergency_worker_id = $emergencyWorker->id;
      $returnable->emergency_worker_name = $emergencyWorker->getCompleteName();
    }
    return $returnable;
  }

  /**
   * @return array
   * @noinspection SqlNoDataSourceInspection SqlResolve
   */
  #[ArrayShape(["id" => "int", 'name' => "string", 'date' => "string", 'trailer_link' => "string", 'poster_link' => "string", 'booked_tickets' => "int", 'movie_year_id' => "int", 'created_at' => "string", 'updated_at' => "string"])]
  public function getAdminReturnable(): array {
    $returnableMovie = $this->toArray();
    $bookings = [];
    foreach ($this->moviesBookings() as $moviesBooking) {
      $booking = new stdClass();
      $booking->user_id = $moviesBooking->user()->id;
      $booking->firstname = $moviesBooking->user()->firstname;
      $booking->surname = $moviesBooking->user()->surname;
      $booking->amount = $moviesBooking->amount;
      $bookings[] = $booking;
    }

    $usersNotBooked = DB::select('SELECT id, firstname, surname FROM users WHERE users.id 
                                                                     NOT IN (SELECT mb.user_id FROM movies_bookings mb 
                                                                     WHERE mb.movie_id = ' . $this->id . ')');

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
