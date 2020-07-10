<?php

namespace App\Repositories\Broadcast\BroadcastDraft;

use App\Models\Broadcasts\Broadcast;
use App\Models\Broadcasts\BroadcastDraft;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

interface IBroadcastDraftRepository
{

  /**
   * @return BroadcastDraft[]|Collection
   */
  public function getAllBroadcastDraftsOrderedByDate();

  /**
   * @param int $id
   * @return BroadcastDraft | null
   */
  public function getBroadcastDraftById(int $id);

  /**
   * @param BroadcastDraft $draft
   * @return stdClass
   */
  public function getBroadcastDraftReturnable(BroadcastDraft $draft): stdClass;

  /**
   * @param string $subject
   * @param string $bodyHTML
   * @param string $body
   * @param int $writerId
   * @param BroadcastDraft|null $draft
   * @return BroadcastDraft | null
   */
  public function createOrUpdate(string $subject, string $bodyHTML, string $body, int $writerId, BroadcastDraft $draft = null);

  /**
   * @param BroadcastDraft $draft
   * @return bool|null
   */
  public function delete(BroadcastDraft $draft);

}