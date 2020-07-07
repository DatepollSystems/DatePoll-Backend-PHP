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
   * @return Broadcast|stdClass
   */
  public function getBroadcastAdminReturnable(Broadcast $broadcast);

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
   * @param $groups
   * @param $subgroups
   * @param bool $forEveryone
   * @return Broadcast | null
   * @throws Exception
   */
  public function create(string $subject, string $bodyHTML, string $body, int $writerId, $groups, $subgroups, bool $forEveryone);

  /**
   * @param Broadcast $broadcast
   * @return bool|null
   * @throws Exception
   */
  public function delete(Broadcast $broadcast);

}