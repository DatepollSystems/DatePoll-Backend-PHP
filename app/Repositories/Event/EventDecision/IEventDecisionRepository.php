<?php

namespace App\Repositories\Event\EventDecision;


use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\User\User;
use Exception;
use stdClass;

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
   * @param string $color ;
   * @param EventDecision|null $eventDecision
   * @return EventDecision
   */
  public function createOrUpdateEventDecision(Event $event, string $decision, bool $showInCalendar, string $color, EventDecision $eventDecision = null);

  /**
   * @param User $user
   * @param Event $event
   * @param bool $anonymous
   * @return stdClass
   */
  public function getDecisionForUser(User $user, Event $event, $anonymous = true);

  /**
   * @param int $eventId
   * @param int $userId
   * @return null|EventUserVotedForDecision
   */
  public function getEventUserVotedForDecisionByEventIdAndUserId(int $eventId, int $userId);
}