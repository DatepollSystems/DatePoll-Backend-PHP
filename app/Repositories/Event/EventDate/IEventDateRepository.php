<?php

namespace App\Repositories\Event\EventDate;

use App\Models\Events\Event;
use App\Models\Events\EventDate;
use Exception;

interface IEventDateRepository
{

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
   * @param double $x
   * @param double $y
   * @param string $date
   * @param string $location
   * @param string $description
   * @return null | EventDate
   */
  public function createEventDate(Event $event, $x, $y, string $date, string $location, string $description);

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