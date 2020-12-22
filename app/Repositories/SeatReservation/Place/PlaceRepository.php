<?php

namespace App\Repositories\SeatReservation\Place;

use App\Logging;
use App\Models\SeatReservation\Place;
use Exception;

class PlaceRepository implements IPlaceRepository {

  /**
   * @param int $id
   * @return Place|null
   */
  public function getPlaceById(int $id): ?Place {
    return Place::find($id);
  }

  /**
   * @return Place[]
   */
  public function getAllPlaces(): array {
    return Place::all()->all();
  }

  /**
   * @param string $name
   * @param double $x
   * @param double $y
   * @param Place|null $place
   * @return Place|null
   */
  public function createOrUpdatePlace(string $name, float $x, float $y, Place $place = null): ?Place {
    if ($place == null) {
      $place = new Place(['name' => $name, 'x' => $x, 'y' => $y]);
    } else {
      $place->name = $name;
      $place->x = $x;
      $place->y = $y;
    }

    if (! $place->save()) {
      Logging::error('createOrUpdatePlace', 'Could not save place!');

      return null;
    }

    return $place;
  }

  /**
   * @param Place $place
   * @return bool
   * @throws Exception
   */
  public function deletePlace(Place $place): bool {
    return $place->delete();
  }
}
