<?php


namespace App\Repositories\SeatReservation\Place;

use App\Logging;
use App\Models\SeatReservation\Place;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class PlaceRepository implements IPlaceRepository {

  /**
   * @param int $id
   * @return Place|null
   */
  public function getPlaceById(int $id) {
    return Place::find($id);
  }

  /**
   * @return Place[]|Collection
   */
  public function getAllPlaces() {
    return Place::all();
  }

  /**
   * @param string $name
   * @param double $x
   * @param double $y
   * @param Place|null $place
   * @return Place|null
   */
  public function createOrUpdatePlace(string $name, float $x, float $y, Place $place = null) {
    if ($place == null) {
      $place = new Place(['name' => $name, 'x' => $x, 'y' => $y]);
    } else {
      $place->name = $name;
      $place->x = $x;
      $place->y = $y;
    }

    if (!$place->save()) {
      Logging::error('createOrUpdatePlace', 'Could not save place!');
      return null;
    }

    return $place;
  }

  /**
   * @param Place $place
   * @return bool|null
   * @throws Exception
   */
  public function deletePlace(Place $place) {
    return $place->delete();
  }
}
