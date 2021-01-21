<?php

namespace App\Repositories\Event\EventStandardLocation;

use App\Models\Events\EventStandardLocation;

interface IEventStandardLocationRepository {
  /**
   * @return EventStandardLocation[]
   */
  public function getAllStandardLocationsOrderedByName(): array;

  /**
   * @param int $id
   * @return EventStandardLocation | null
   */
  public function getStandardLocationById(int $id): ?EventStandardLocation;

  /**
   * @param string $name
   * @param string|null $location
   * @param string|null $x
   * @param string|null $y
   * @return EventStandardLocation|null
   */
  public function createStandardLocation(string $name, ?string $location, ?string $x, ?string $y): ?EventStandardLocation;

  /**
   * @param int $standardLocationId
   * @return bool
   */
  public function deleteStandardLocation(int $standardLocationId): bool;
}
