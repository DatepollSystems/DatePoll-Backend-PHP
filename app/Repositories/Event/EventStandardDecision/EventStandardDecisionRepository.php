<?php

namespace App\Repositories\Event\EventStandardDecision;

use App\Models\Events\EventStandardDecision;

class EventStandardDecisionRepository implements IEventStandardDecisionRepository {
  /**
   * @return EventStandardDecision[]
   */
  public function getAllStandardDecisionsOrderedByName(): array {
    return EventStandardDecision::orderBy('decision')
      ->get()->all();
  }

  /**
   * @param int $id
   * @return EventStandardDecision | null
   */
  public function getStandardDecisionById(int $id): ?EventStandardDecision {
    return EventStandardDecision::find($id);
  }

  /**
   * @param string $decision
   * @param bool $showInCalendar
   * @param string $color
   * @return EventStandardDecision|null
   */
  public function createStandardDecision(string $decision, bool $showInCalendar, string $color): ?EventStandardDecision {
    $standardDecision = new EventStandardDecision([
      'decision' => $decision,
      'showInCalendar' => $showInCalendar,
      'color' => $color, ]);

    if (! $standardDecision->save()) {
      return null;
    }

    return $standardDecision;
  }

  /**
   * @param int $standardDecisionId
   * @return bool
   */
  public function deleteStandardDecision(int $standardDecisionId): bool {
    return (EventStandardDecision::destroy($standardDecisionId) > 0);
  }
}
