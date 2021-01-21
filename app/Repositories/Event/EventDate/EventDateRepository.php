<?php

namespace App\Repositories\Event\EventDate;

use App\Models\Events\Event;
use App\Models\Events\EventDate;
use Exception;

class EventDateRepository implements IEventDateRepository {
  /**
   * @param EventDate $eventDate
   * @return bool
   * @throws Exception
   */
  public function deleteEventDate(EventDate $eventDate): bool {
    return $eventDate->delete();
  }

  /**
   * @param Event $event
   * @param float|null $x
   * @param float|null $y
   * @param string|null $date
   * @param string|null $location
   * @param string|null $description
   * @return null | EventDate
   */
  public function createEventDate(Event $event, ?float $x, ?float $y, ?string $date, ?string $location, ?string $description): ?EventDate {
    $eventDate = new EventDate([
      'event_id' => $event->id,
      'x' => $x,
      'y' => $y,
      'date' => $date,
      'location' => $location,
      'description' => $description, ]);

    return $eventDate->save() ? $eventDate : null;
  }
}
