<?php


namespace App\Repositories\Event\EventDecision;


use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use Exception;

class EventDecisionRepository implements IEventDecisionRepository
{
  /**
   * @param int $id
   * @return EventDecision
   */
  public function getEventDecisionById(int $id) {
    return EventDecision::find($id);
  }

  /**
   * @param EventDecision $decision
   * @throws Exception
   */
  public function deleteEventDecision(EventDecision $decision) {
    $decision->delete();
  }

  /**
   * @param Event $event
   * @param string $decision
   * @param bool $showInCalendar
   * @return EventDecision
   * @throws Exception
   */
  public function createEventDecision(Event $event, string $decision, bool $showInCalendar) {
    $eventDecision = new EventDecision([
      'event_id' => $event->id,
      'decision' => $decision,
      'showInCalendar' => $showInCalendar]);

    return $eventDecision->save() ? $eventDecision : null;
  }

}