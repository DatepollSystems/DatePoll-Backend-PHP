<?php

namespace App\Repositories\Event\Event;

use App\Models\Events\Event;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\User\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

interface IEventRepository {

  /**
   * @return Event[]|Collection
   */
  public function getAllEvents();

  /**
   * @return int[]
   */
  public function getYearsOfEvents(): array;

  /**
   * @param int|null $year
   * @return Event[]
   */
  public function getEventsOrderedByDate(int $year = null): array;

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

  /**
   * @param Event $event
   * @return stdClass
   */
  public function getReturnable(Event $event);

  /**
   * @param Event $event
   * @param bool $anonymous
   * @return stdClass
   */
  public function getResultsForEvent(Event $event, bool $anonymous);

  /**
   * @param User $user
   * @return array
   */
  public function getOpenEventsForUser(User $user);

  /**
   * @param Event $event
   * @return array
   */
  public function getPotentialVotersForEvent(Event $event);

  /**
   * @param EventUserVotedForDecision|null $eventUserVotedForDecision
   * @return stdClass|null
   */
  public function getUserDecisionReturnable(?EventUserVotedForDecision $eventUserVotedForDecision);
}
