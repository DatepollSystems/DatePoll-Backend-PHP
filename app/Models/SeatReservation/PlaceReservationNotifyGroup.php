<?php

namespace App\Models\SeatReservation;

use App\Models\Groups\Group;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property Group $group
 * @property int $group_id
 * @property string $created_at
 * @property string $updated_at
 */
class PlaceReservationNotifyGroup extends Model {
  protected $table = 'place_reservation_notify_groups';

  /**
   * @var array
   */
  protected $fillable = [
    'group_id',
    'created_at',
    'updated_at', ];

  /**
   * @return Group | BelongsTo
   */
  public function group(): Group|BelongsTo {
    return $this->hasOne(Group::class)->get()->first();
  }
}
