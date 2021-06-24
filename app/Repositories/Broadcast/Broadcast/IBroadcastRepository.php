<?php

namespace App\Repositories\Broadcast\Broadcast;

use App\Models\Broadcasts\Broadcast;
use App\Models\User\User;
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
   * @param User $user
   * @param int $limit
   * @param int $page
   * @return Broadcast[]
   */
  public function getBroadcastsForUserOrderedByDate(User $user, int $limit = -1, int $page = -1): array;

  /**
   * @param string $search
   * @return array
   */
  public function searchBroadcasts(string $search): array;

  /**
   * @param User $user
   * @param Broadcast $broadcast
   * @return bool
   */
  public function isUserAllowedToViewBroadcast(User $user, Broadcast $broadcast): bool;
}
