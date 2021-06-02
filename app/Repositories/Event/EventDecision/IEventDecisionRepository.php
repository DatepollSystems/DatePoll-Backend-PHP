<?php

namespace App\Repositories\Event\EventDecision;

use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use JetBrains\PhpStorm\ArrayShape;
use App\Models\User\User;
use Exception;

interface IEventDecisionRepository {
  /**
   * @param EventDecision $decision
   * @throws Exception
   */
  public function deleteEventDecision(EventDecision $decision);

  /**
   * @param Event $event
   * @param string $decision
   * @param bool $showInCalendar
   * @param string $color ;
   * @param EventDecision|null $eventDecision
   * @return EventDecision|null
   */
  public function createOrUpdateEventDecision(Event $event, string $decision, bool $showInCalendar, string $color, EventDecision $eventDecision = null): ?EventDecision;

  /**
   * @param int $id
   * @return EventDecision[]
   */
  public function getEventDecisionsByEventId(int $id): array;

  /**
   * @param User $user
   * @param Event $event
   * @return array
   */
  #[ArrayShape(['id' => "int|null", 'firstname' => "null|string", 'surname' => "null|string",
                'decisionId' => "mixed", 'decision' => "mixed",
                'additional_information' => "mixed"])]
  public function getDecisionForUser(User $user, Event $event): array;
}
