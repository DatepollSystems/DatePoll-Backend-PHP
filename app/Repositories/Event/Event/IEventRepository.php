<?php

namespace App\Repositories\Event\Event;

use App\Models\Events\Event;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IEventRepository
{

  /**
   * @return Event[]|Collection
   */
  public function getAllEvents();

  /**
   * @return Event[]
   */
  public function getAllEventsOrderedByDate();

  /**
   * @param int $id
   * @return Event
   */
  public function getEventById(int $id);

  /**
   * @param string $name
   * @param bool $forEveryone
   * @param string $description
   * @param array $decisions
   * @param array $dates
   * @param Event|null $event
   * @return Event|null
   * @throws Exception
   */
  public function createOrUpdateEvent(string $name, bool $forEveryone, string $description, array $decisions, array $dates, Event $event = null);

  /**
   * @param Event $event
   * @return bool
   * @throws Exception
   */
  public function deleteEvent(Event $event);
}