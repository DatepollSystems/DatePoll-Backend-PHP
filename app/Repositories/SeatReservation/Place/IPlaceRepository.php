<?php

namespace App\Repositories\SeatReservation\Place;

use App\Models\SeatReservation\Place;
use Exception;

interface IPlaceRepository {

  /**
   * @param int $id
   * @return Place|null
   */
  public function getPlaceById(int $id): ?Place;

  /**
   * @return Place[]
   */
  public function getAllPlaces(): array;

  /**
   * @param string $name
   * @param string|null $location
   * @param double $x
   * @param double $y
   * @param Place|null $place
   * @return Place|null
   */
  public function createOrUpdatePlace(string $name, ?string $location, float $x, float $y, Place $place = null): ?Place;

  /**
   * @param Place $place
   * @return bool
   * @throws Exception
   */
  public function deletePlace(Place $place): bool;
}
