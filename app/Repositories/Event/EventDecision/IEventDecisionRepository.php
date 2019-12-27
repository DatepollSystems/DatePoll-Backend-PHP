<?php

namespace App\Repositories\Event\EventDecision;


use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use Exception;

interface IEventDecisionRepository
{
  /**
   * @param int $id
   * @return EventDecision
   */
  public function getEventDecisionById(int $id);

  /**
   * @param EventDecision $decision
   * @throws Exception
   */
  public function deleteEventDecision(EventDecision $decision);

  /**
   * @param Event $event
   * @param string $decision
   * @param bool $showInCalendar
   * @return EventDecision
   * @throws Exception
   */
  public function createEventDecision(Event $event, string $decision, bool $showInCalendar);

}