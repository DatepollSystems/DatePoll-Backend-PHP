<?php

namespace App\Repositories\Event\EventStandardDecision;

use App\Models\Events\EventStandardDecision;

interface IEventStandardDecisionRepository {
  /**
   * @return EventStandardDecision[]
   */
  public function getAllStandardDecisionsOrderedByName(): array;

  /**
   * @param int $id
   * @return EventStandardDecision | null
   */
  public function getStandardDecisionById(int $id): ?EventStandardDecision;

  /**
   * @param string $decision
   * @param bool $showInCalendar
   * @param string $color
   * @return EventStandardDecision|null
   */
  public function createStandardDecision(string $decision, bool $showInCalendar, string $color): ?EventStandardDecision;

  /**
   * @param int $standardDecisionId
   * @return bool
   */
  public function deleteStandardDecision(int $standardDecisionId): bool;
}
