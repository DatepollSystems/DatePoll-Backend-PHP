<?php

namespace App\Repositories\Broadcast\Broadcast;

use App\Models\Broadcasts\Broadcast;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

interface IBroadcastRepository
{

  /**
   * @return Broadcast[]|Collection
   */
  public function getAllBroadcastsOrderedByDate();

  /**
   * @param int $id
   * @return Broadcast | null
   */
  public function getBroadcastById(int $id);

  /**
   * @param Broadcast $broadcast
   * @return stdClass | Broadcast
   */
  public function getBroadcastUserReturnable(Broadcast $broadcast);

  /**
   * @param Broadcast $broadcast
   * @return stdClass | Broadcast
   */
  public function getBroadcastReturnable(Broadcast $broadcast);

  /**
   * @param Broadcast $broadcast
   * @return Broadcast|stdClass
   */
  public function getBroadcastSentReceiptReturnable(Broadcast $broadcast);

  /**
   * @param string $subject
   * @param string $bodyHTML
   * @param string $body
   * @param int $writerId
   * @param int[] $groups
   * @param int[] $subgroups
   * @param bool $forEveryone
   * @return Broadcast | null
   * @throws Exception
   */
  public function create(string $subject, string $bodyHTML, string $body, int $writerId, $groups, $subgroups, bool $forEveryone);

  /**
   * @param Broadcast $broadcast
   * @throws Exception
   */
  public function reQueueNotSentBroadcastsForBroadcast(Broadcast $broadcast);

  /**
   * @param Broadcast $broadcast
   * @return bool|null
   * @throws Exception
   */
  public function delete(Broadcast $broadcast);

  /**
   * @param int $userId
   * @param int $limit
   * @return Broadcast[]
   */
  public function getBroadcastsForUserByIdOrderedByDate(int $userId, int $limit = -1);

  /**
   * @param int $userId
   * @param int $broadcastId
   * @return bool
   */
  public function isUserByIdAllowedToViewBroadcastById(int $userId, int $broadcastId);

}
