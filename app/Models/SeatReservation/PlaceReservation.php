<?php /** @noinspection PhpMultipleClassesDeclarationsInOneFile */

namespace App\Models\SeatReservation;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use stdClass;

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
class PlaceReservation extends Model
{

  /**
   * The table associated with the model.
   *
   * @var string
   */
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
    'created_at',
    'updated_at'];

  /**
   * @return BelongsTo | Place
   */
  public function place() {
    return $this->belongsTo(Place::class, 'place_id')
      ->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function user() {
    return $this->belongsTo(User::class, 'user_id')
      ->first();
  }

  /**
   * @return BelongsTo | User
   */
  public function approver() {
    return $this->belongsTo(User::class, 'approver_id')
      ->first();
  }

  /**
   * @return PlaceReservation | stdClass
   */
  public function getReturnable(): PlaceReservation {
    $returnable = $this;
    $returnable->user_name = $this->user()->getName();
    $returnable->approver_name = $this->approver()->getName();
    $returnable->place_name = $this->place()->name;
    return $returnable;
  }
}

abstract class PlaceReservationState
{
  const WAITING = "WAITING";
  const APPROVED = "APPROVED";
  const REJECTED = "REJECTED";
}
