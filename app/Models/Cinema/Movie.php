<?php

namespace App\Models\Cinema;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use JetBrains\PhpStorm\ArrayShape;

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
  public static string $workerNumberProperty = 'worker_numbers';
  private static string $emergencyWorkerIdProperty = 'emergency_worker_id';
  private static string $emergencyWorkerNameProperty = 'emergency_worker_name';
  public static string $emergencyWorkerNumberProperty = 'emergency_worker_numbers';

  /**
   * @var array
   */
  protected $fillable = ['worker_id', 'emergency_worker_id', 'name', 'date', 'trailerLink', 'posterLink',
                         'bookedTickets', 'maximalTickets', 'created_at', 'updated_at'];

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
    $movieBookingForYourself = MoviesBooking::where('movie_id', $this->id)->where('user_id', $userId)->first();

    if ($movieBookingForYourself == null) {
      return 0;
    }

    return $movieBookingForYourself->amount;
  }

  /**
   * @return array
   */
  #[ArrayShape(["id" => "int", 'name' => "string", 'date' => "string", 'trailer_link' => "string",
                'poster_link' => "string", 'booked_tickets' => "int", 'maximal_tickets' => "int",
                'created_at' => "string",
                'updated_at' => "string", 'worker_id' => "int", 'worker_name' => 'string',
                'emergency_worker_id' => 'int', 'emergency_worker_name' => 'string'])]
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

    $returnable[self::$workerIdProperty] = $worker?->id;
    $returnable[self::$workerNameProperty] = $worker?->getCompleteName();
    if ($worker == null) {
      $returnable[self::$workerNumberProperty] = [];
    } else {
      $returnable[self::$workerNumberProperty] = $worker?->telephoneNumbers();
    }
    $returnable[self::$emergencyWorkerIdProperty] = $emergencyWorker?->id;
    $returnable[self::$emergencyWorkerNameProperty] = $emergencyWorker?->getCompleteName();
    if ($emergencyWorker == null) {
      $returnable[self::$emergencyWorkerNumberProperty] = [];
    } else {
      $returnable[self::$emergencyWorkerNumberProperty] = $emergencyWorker->telephoneNumbers();
    }

    return $returnable;
  }
}
