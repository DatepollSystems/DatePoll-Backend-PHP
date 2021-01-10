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
 * @property int|null $worker_id
 * @property string|null $worker_name
 * @property int|null $emergency_worker_id
 * @property string|null $emergency_worker_name
 * @property string $name
 * @property string $date
 * @property string $trailerLink
 * @property string $posterLink
 * @property int $bookedTickets
 * @property int $maximalTickets
 * @property string $created_at
 * @property string $updated_at
 * @property User $emergencyWorker
 * @property User $worker
 * @property MoviesBooking[] $moviesBookings
 */
class Movie extends Model {
  private static string $workerIdProperty = 'worker_id';
  private static string $workerNameProperty = 'worker_name';
  private static string $emergencyWorkerIdProperty = 'emergency_worker_id';
  private static string $emergencyWorkerNameProperty = 'emergency_worker_name';

  /**
   * @var array
   */
  protected $fillable = ['worker_id', 'emergency_worker_id', 'name', 'date', 'trailerLink', 'posterLink', 'bookedTickets', 'maximalTickets', 'created_at', 'updated_at'];

  /**
   * @return BelongsTo | User | null
   */
  public function emergencyWorker(): BelongsTo|User|null {
    return $this->belongsTo(User::class, 'emergency_worker_id')->first();
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
    return $this->hasMany(MoviesBooking::class)->get()->all();
  }

  /**
   * @param int $userId
   * @return int
   */
  public function getBookedTicketsForUser(int $userId): int {
    $movieBookingForYourself = MoviesBooking::where('movie_id', $this->id)->where('user_id',$userId)->first();

    if ($movieBookingForYourself == null) {
      return 0;
    } else {
      return $movieBookingForYourself->amount;
    }
  }

  /**
   * @return array
   */
  #[ArrayShape(["id" => "int", 'name' => "string", 'date' => "string", 'trailer_link' => "string",
    'poster_link' => "string", 'booked_tickets' => "int", 'maximal_tickets' => "int", 'created_at' => "string",
    'updated_at' => "string", 'worker_id' => "int", 'worker_name' => 'string', 'emergency_worker_id' => 'int', 'emergency_worker_name' => 'string'])]
  public function toArray(): array {
    $returnable = [
      "id" => $this->id,
      'name' => $this->name,
      'date' => $this->date,
      'trailer_link' => $this->trailerLink,
      'poster_link' => $this->posterLink,
      'booked_tickets' => $this->bookedTickets,
      'maximal_tickets' => $this->maximalTickets,
      'created_at' => $this->created_at,
      'updated_at' => $this->updated_at
    ];

    $worker = $this->worker();
    $emergencyWorker = $this->emergencyWorker();

    if ($worker == null) {
      $returnable[$this::$workerIdProperty] = null;
      $returnable[$this::$workerNameProperty] = null;
    } else {
      $returnable[$this::$workerIdProperty] = $worker->id;
      $returnable[$this::$workerNameProperty] = $worker->getCompleteName();
    }

    if ($emergencyWorker == null) {
      $returnable[$this::$emergencyWorkerIdProperty] = null;
      $returnable[$this::$emergencyWorkerNameProperty] = null;
    } else {
      $returnable[$this::$emergencyWorkerIdProperty] = $emergencyWorker->id;
      $returnable[$this::$emergencyWorkerNameProperty] = $emergencyWorker->getCompleteName();
    }
    return $returnable;
  }

  /**
   * @return array
   * @noinspection SqlNoDataSourceInspection SqlResolve
   */
  #[ArrayShape(["id" => "int", 'name' => "string", 'date' => "string", 'trailer_link' => "string",
    'poster_link' => "string", 'booked_tickets' => "int", 'movie_year_id' => "int", 'created_at' => "string",
    'updated_at' => "string", 'worker_id' => "int", 'worker_name' => 'string', 'emergency_worker_id' => 'int',
    'emergency_worker_name' => 'string', 'bookings' => 'array'])]
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

    usort($bookings, function ($a, $b) {
      return strcmp($b->amount, $a->amount);
    });

    $returnableMovie['bookings'] = $bookings;

    return $returnableMovie;
  }
}
