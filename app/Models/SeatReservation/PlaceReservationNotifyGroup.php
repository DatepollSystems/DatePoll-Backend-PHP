<?php

namespace App\Models\SeatReservation;

use App\Models\Groups\Group;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property Group $group
 * @property int $group_id
 * @property string $created_at
 * @property string $updated_at
 */
class PlaceReservationNotifyGroup extends Model {

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'place_reservation_notify_groups';

  /**
   * @var array
   */
  protected $fillable = [
    'group_id',
    'created_at',
    'updated_at', ];

  /**
   * @return Collection | PlaceReservation[] | null
   */
  public function getGroups() {
    return $this->hasMany(Group::class)->get();
  }
}
