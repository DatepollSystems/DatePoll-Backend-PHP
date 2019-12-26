<?php


namespace App\Repositories\Event\EventDate;

use App\Models\Events\Event;
use App\Models\Events\EventDate;

class EventDateRepository implements IEventDateRepository
{

  /**
   * @param Event $event
   * @return mixed | EventDate[]
   */
  public function getEventDatesForEvent(Event $event) {
    return EventDate::where('event_id', '=', $event->id)
                    ->orderBy('date')
                    ->get();
  }
}