<?php

namespace App\Repositories\Event\EventStandardLocation;

use App\Models\Events\EventStandardLocation;

interface IEventStandardLocationRepository
{
  /**
   * @return EventStandardLocation[]
   */
  public function getAllStandardLocationsOrderedByName();

  /**
   * @param int $id
   * @return EventStandardLocation | null
   */
  public function getStandardLocationById(int $id);

  /**
   * @param string $name
   * @param string $location
   * @param double $x
   * @param double $y
   * @return EventStandardLocation|null
   */
  public function createStandardLocation($name, $location, $x, $y);

  /**
   * @param int $id
   * @return int
   */
  public function deleteStandardLocation(int $id);

}