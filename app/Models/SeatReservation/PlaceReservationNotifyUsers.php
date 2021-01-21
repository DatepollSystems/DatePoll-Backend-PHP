<?php

namespace App\Models\SeatReservation;

use App\Models\User\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property User $user
 * @property Place $place
 * @property int $user_id
 * @property int $place_id
 * @property string $created_at
 * @property string $updated_at
 */
class PlaceReservationNotifyUsers extends Model {
  protected $table = 'place_reservation_notify_users';

  /**
   * @var array
   */
  protected $fillable = [
    'user_id',
    'place_id',
    'created_at',
    'updated_at', ];

  /**
   * @return User|BelongsTo
   */
  public function user(): BelongsTo|User {
    return $this->belongsTo(User::class)->first();
  }

  /**
   * @return Place|BelongsTo
   */
  public function place(): BelongsTo|Place {
    return $this->belongsTo(Place::class)->first();
  }
}
