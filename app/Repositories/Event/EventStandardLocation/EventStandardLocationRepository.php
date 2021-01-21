<?php

namespace App\Repositories\Event\EventStandardLocation;

use App\Models\Events\EventStandardLocation;

class EventStandardLocationRepository implements IEventStandardLocationRepository {
  /**
   * @return EventStandardLocation[]
   */
  public function getAllStandardLocationsOrderedByName(): array {
    return EventStandardLocation::orderBy('name')
      ->get()->all();
  }

  /**
   * @param int $id
   * @return EventStandardLocation | null
   */
  public function getStandardLocationById(int $id): ?EventStandardLocation {
    return EventStandardLocation::find($id);
  }

  /**
   * @param string $name
   * @param string|null $location
   * @param string|null $x
   * @param string|null $y
   * @return EventStandardLocation|null
   */
  public function createStandardLocation(string $name, ?string $location, ?string $x, ?string $y): ?EventStandardLocation {
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
   * @param int $standardLocationId
   * @return bool
   */
  public function deleteStandardLocation(int $standardLocationId): bool {
    return (EventStandardLocation::destroy($standardLocationId) > 0);
  }
}
