<?php

namespace App\Models\SeatReservation;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property double $x
 * @property double $y
 * @property string $created_at
 * @property string $updated_at
 * @property PlaceReservation[] $placeReservations
 */
class Place extends Model {

  /**
   * The table associated with the model.
   *
   * @var string
   */
  protected $table = 'places';

  /**
   * @var array
   */
  protected $fillable = [
    'name',
    'x',
    'y',
    'created_at',
    'updated_at', ];

  /**
   * @return Collection | PlaceReservation[] | null
   */
  public function placeReservations() {
    return $this->hasMany(PlaceReservation::class)
      ->get();
  }
}
