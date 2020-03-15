<?php

namespace App\Repositories\Event\EventStandardDecision;

use App\Models\Events\EventStandardDecision;

interface IEventStandardDecisionRepository
{
  /**
   * @return EventStandardDecision[]
   */
  public function getAllStandardDecisionsOrderedByName();

  /**
   * @param int $id
   * @return EventStandardDecision | null
   */
  public function getStandardDecisionById(int $id);

  /**
   * @param string $decision
   * @param bool $showInCalendar
   * @param string $color
   * @return EventStandardDecision|null
   */
  public function createStandardDecision($decision, $showInCalendar, $color);

  /**
   * @param int $id
   * @return int
   */
  public function deleteStandardDecision(int $id);
}