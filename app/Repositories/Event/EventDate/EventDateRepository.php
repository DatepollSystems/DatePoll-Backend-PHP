<?php

namespace App\Repositories\Event\EventDate;

use App\Models\Events\Event;
use App\Models\Events\EventDate;
use Exception;

class EventDateRepository implements IEventDateRepository {

  /**
   * @param Event $event
   * @return mixed | EventDate[]
   */
  public function getEventDatesOrderedByDateForEvent(Event $event) {
    return EventDate::where('event_id', '=', $event->id)
      ->orderBy('date')
      ->get();
  }

  /**
   * @param EventDate $eventDate
   * @return bool|null
   * @throws Exception
   */
  public function deleteEventDate(EventDate $eventDate) {
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

  /**
   * @param Event $event
   * @return EventDate | null
   */
  public function getFirstEventDateForEvent(Event $event) {
    return EventDate::where('event_id', '=', $event->id)
      ->orderBy('date', 'ASC')
      ->first();
  }

  /**
   * @param Event $event
   * @return EventDate | null
   */
  public function getLastEventDateForEvent(Event $event) {
    return EventDate::where('event_id', '=', $event->id)
      ->latest('date')
      ->first();
  }
}
