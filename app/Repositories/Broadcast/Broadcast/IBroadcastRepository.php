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
  public function getAllBroadcasts();

  /**
   * @return Broadcast[]|Collection
   */
  public function getAllBroadcastsOrderedByDate();

  /**
   * @param Broadcast $broadcast
   * @return Broadcast|stdClass
   */
  public function getBroadcastAdminReturnable(Broadcast $broadcast);

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
}