<?php

namespace App\Repositories\Broadcast\Broadcast;

use App\Models\Broadcasts\Broadcast;
use App\Repositories\Interfaces\IHasYearsRepository;
use Exception;

interface IBroadcastRepository extends IHasYearsRepository {

  /**
   * @param int $id
   * @return Broadcast | null
   */
  public function getBroadcastById(int $id): ?Broadcast;

  /**
   * @param string $subject
   * @param string $bodyHTML
   * @param string $body
   * @param int $writerId
   * @param array $groups
   * @param array $subgroups
   * @param bool $forEveryone
   * @param array $attachments
   * @return Broadcast | null
   */
  public function create(
    string $subject,
    string $bodyHTML,
    string $body,
    int $writerId,
    array $groups,
    array $subgroups,
    bool $forEveryone,
    array $attachments
  ): ?Broadcast;

  /**
   * @param Broadcast $broadcast
   * @throws Exception
   */
  public function reQueueNotSentBroadcastsForBroadcast(Broadcast $broadcast);

  /**
   * @param Broadcast $broadcast
   * @return bool
   * @throws Exception
   */
  public function delete(Broadcast $broadcast): bool;

  /**
   * @param int $userId
   * @param int|null $limit
   * @param int|null $page
   * @return Broadcast[]
   */
  public function getBroadcastsForUserOrderedByDate(int $userId, ?int $limit = null, ?int $page = null): array;

  /**
   * @param string $search
   * @return array
   */
  public function searchBroadcasts(string $search): array;

  /**
   * @param int $userId
   * @param Broadcast $broadcast
   * @return bool
   */
  public function isUserAllowedToViewBroadcast(int $userId, Broadcast $broadcast): bool;
}
