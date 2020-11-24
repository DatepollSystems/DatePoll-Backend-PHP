<?php

namespace App\Repositories\Event\EventStandardLocation;

use App\Models\Events\EventStandardLocation;
use Illuminate\Database\Eloquent\Collection;

class EventStandardLocationRepository implements IEventStandardLocationRepository {
  /**
   * @return Collection<EventStandardLocation>
   */
  public function getAllStandardLocationsOrderedByName() {
    return EventStandardLocation::orderBy('name')
      ->get();
  }

  /**
   * @param int $id
   * @return EventStandardLocation | null
   */
  public function getStandardLocationById(int $id) {
    return EventStandardLocation::find($id);
  }

  /**
   * @param string $name
   * @param string $location
   * @param double $x
   * @param double $y
   * @return EventStandardLocation|null
   */
  public function createStandardLocation($name, $location, $x, $y) {
    $standardLocation = new EventStandardLocation([
      'name' => $name,
      'location' => $location,
      'x' => $x,
      'y' => $y, ]);

    if (! $standardLocation->save()) {
      return null;
    }

    return $standardLocation;
  }

  /**
   * @param int $id
   * @return int
   */
  public function deleteStandardLocation(int $id) {
    return EventStandardLocation::destroy($id);
  }
}
