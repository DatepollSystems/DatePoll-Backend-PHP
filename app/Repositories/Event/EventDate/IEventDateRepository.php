<?php

namespace App\Repositories\Event\EventDate;

use App\Models\Events\Event;
use App\Models\Events\EventDate;

interface IEventDateRepository
{

  /**
   * @param Event $event
   * @return mixed | EventDate[]
   */
  public function getEventDatesForEvent(Event $event);

}