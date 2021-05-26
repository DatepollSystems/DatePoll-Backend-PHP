<?php

namespace App\Repositories\Event\Event;

use App\Models\Events\Event;
use App\Models\User\User;
use App\Repositories\Interfaces\IHasYearsRepository;
use Exception;

interface IEventRepository extends IHasYearsRepository {

  /**
   * @return Event[]
   */
  public function getAllEvents(): array;

  /**
   * @param int $id
   * @return Event|null
   */
  public function getEventById(int $id): ?Event;

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
  public function createOrUpdateEvent(string $name, bool $forEveryone, string $description, array $decisions, array $dates, Event $event = null): ?Event;

  /**
   * @param Event $event
   * @return bool
   * @throws Exception
   */
  public function deleteEvent(Event $event): bool;

  /**
   * @param Event $event
   * @param bool $anonymous
   * @param bool $calculateCharts
   * @return array
   */
  public function getResultsForEvent(Event $event, bool $anonymous, bool $calculateCharts = false): array;

  /**
   * @param User $user
   * @return array
   */
  public function getOpenEventsForUser(User $user): array;

  /**
   * @param Event $event
   * @return array
   */
  public function getPotentialVotersForEvent(Event $event): array;
}
