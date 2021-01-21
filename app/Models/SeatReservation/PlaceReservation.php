<?php /** @noinspection PhpMultipleClassesDeclarationsInOneFile */

namespace App\Models\SeatReservation;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $reason
 * @property string $description
 * @property string $start_date
 * @property string $end_date
 * @property string $state
 * @property int $place_id
 * @property Place $place
 * @property int $user_id
 * @property User $user
 * @property int $approver_id
 * @property User $approver
 * @property string $created_at
 * @property string $updated_at
 */
class PlaceReservation extends Model {
  protected $table = 'places_reservations_by_users';

  /**
   * @var array
   */
  protected $fillable = [
    'reason',
    'description',
    'start_date',
    'end_date',
    'state',
    'user_id',
    'approver_id',
    'place_id',
    'created_at',
    'updated_at', ];

  /**
   * @return BelongsTo | Place
   */
  public function place(): BelongsTo|Place {
    return $this->belongsTo(Place::class, 'place_id')
      ->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class, 'user_id')
      ->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function approver(): BelongsTo|User {
    return $this->belongsTo(User::class, 'approver_id')
      ->first();
  }

  /**
   * @return array
   */
  public function toArray(): array {
    $returnable = parent::toArray();
    $returnable['user_name'] = $this->user()->getCompleteName();
    $returnable['approver_name'] = $this->approver()->getCompleteName();
    $returnable['place_name'] = $this->place()->name;
    return $returnable;
  }
}

abstract class PlaceReservationState {
  public const WAITING = 'WAITING';
  public const APPROVED = 'APPROVED';
  public const REJECTED = 'REJECTED';
}
