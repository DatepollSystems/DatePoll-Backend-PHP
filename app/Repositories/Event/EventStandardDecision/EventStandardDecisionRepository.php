<?php


namespace App\Repositories\Event\EventStandardDecision;

use App\Models\Events\EventStandardDecision;
use Illuminate\Support\Collection;

class EventStandardDecisionRepository implements IEventStandardDecisionRepository
{
  /**
   * @return Collection<EventStandardDecision>
   */
  public function getAllStandardDecisionsOrderedByName() {
    return EventStandardDecision::orderBy('decision')
                                ->get();
  }

  /**
   * @param int $id
   * @return EventStandardDecision | null
   */
  public function getStandardDecisionById(int $id) {
    return EventStandardDecision::find($id);
  }

  /**
   * @param string $decision
   * @param bool $showInCalendar
   * @param string $color
   * @return EventStandardDecision|null
   */
  public function createStandardDecision($decision, $showInCalendar, $color) {
    $standardDecision = new EventStandardDecision([
      'decision' => $decision,
      'showInCalendar' => $showInCalendar,
      'color' => $color]);

    if (!$standardDecision->save()) {
      return null;
    }

    return $standardDecision;
  }

  /**
   * @param int $id
   * @return int
   */
  public function deleteStandardDecision(int $id) {
    return EventStandardDecision::destroy($id);
  }
}