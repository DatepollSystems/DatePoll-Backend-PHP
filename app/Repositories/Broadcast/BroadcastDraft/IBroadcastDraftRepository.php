<?php

namespace App\Repositories\Broadcast\BroadcastDraft;

use App\Models\Broadcasts\BroadcastDraft;
use Exception;

interface IBroadcastDraftRepository {

  /**
   * @return BroadcastDraft[]
   */
  public function getAllBroadcastDraftsOrderedByDate(): array;

  /**
   * @param int $id
   * @return BroadcastDraft | null
   */
  public function getBroadcastDraftById(int $id): ?BroadcastDraft;

  /**
   * @param string $subject
   * @param string $bodyHTML
   * @param string $body
   * @param int $writerId
   * @param BroadcastDraft|null $draft
   * @return BroadcastDraft | null
   */
  public function createOrUpdate(
    string $subject,
    string $bodyHTML,
    string $body,
    int $writerId,
    BroadcastDraft $draft = null
  ): ?BroadcastDraft;

  /**
   * @param BroadcastDraft $draft
   * @return bool
   * @throws Exception
   */
  public function delete(BroadcastDraft $draft): bool;
}
