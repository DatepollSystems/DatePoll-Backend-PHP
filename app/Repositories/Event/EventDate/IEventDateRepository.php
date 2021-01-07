<?php

namespace App\Repositories\Event\EventDate;

use App\Models\Events\Event;
use App\Models\Events\EventDate;
use Exception;

interface IEventDateRepository {

  /**
   * @param Event $event
   * @return mixed | EventDate[]
   */
  public function getEventDatesOrderedByDateForEvent(Event $event);

  /**
   * @param EventDate $eventDate
   * @return bool|null
   * @throws Exception
   */
  public function deleteEventDate(EventDate $eventDate);

  /**
   * @param Event $event
   * @param float|null $x
   * @param float|null $y
   * @param string|null $date
   * @param string|null $location
   * @param string|null $description
   * @return null | EventDate
   */
  public function createEventDate(Event $event, ?float $x, ?float $y, ?string $date, ?string $location, ?string $description): ?EventDate;

  /**
   * @param Event $event
   * @return EventDate | null
   */
  public function getFirstEventDateForEvent(Event $event);

  /**
   * @param Event $event
   * @return EventDate | null
   */
  public function getLastEventDateForEvent(Event $event);
}
