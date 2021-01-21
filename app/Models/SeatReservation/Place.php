<?php

namespace App\Models\SeatReservation;

use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property string $location
 * @property double $x
 * @property double $y
 * @property string $created_at
 * @property string $updated_at
 * @property PlaceReservation[] $placeReservations
 * @property PlaceReservationNotifyUsers[] $placeReservationNotifyUsers
 */
class Place extends Model {
  protected $table = 'places';

  /**
   * @var array
   */
  protected $fillable = [
    'name',
    'location',
    'x',
    'y',
    'created_at',
    'updated_at', ];

  /**
   * @return PlaceReservation[]
   */
  public function placeReservations(): array {
    return $this->hasMany(PlaceReservation::class)
      ->get()->all();
  }

  /**
   * @return PlaceReservationNotifyUsers[]
   */
  public function placeReservationNotifyUsers(): array {
    return $this->hasMany(PlaceReservationNotifyUsers::class)
      ->get()->all();
  }
}
