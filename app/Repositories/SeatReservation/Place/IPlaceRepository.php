<?php

namespace App\Repositories\SeatReservation\Place;

use App\Models\SeatReservation\Place;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IPlaceRepository {

  /**
   * @param int $id
   * @return Place|null
   */
  public function getPlaceById(int $id);

  /**
   * @return Place[]|Collection
   */
  public function getAllPlaces();

  /**
   * @param string $name
   * @param double $x
   * @param double $y
   * @param Place|null $place
   * @return Place|null
   */
  public function createOrUpdatePlace(string $name, float $x, float $y, Place $place = null);

  /**
   * @param Place $place
   * @return bool|null
   * @throws Exception
   */
  public function deletePlace(Place $place);
}
