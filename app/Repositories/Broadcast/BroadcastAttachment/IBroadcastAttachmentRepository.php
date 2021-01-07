<?php

namespace App\Repositories\Broadcast\BroadcastAttachment;

use App\Models\Broadcasts\BroadcastAttachment;
use Exception;

interface IBroadcastAttachmentRepository {

  /**
   * @param int $id
   * @return null|BroadcastAttachment
   */
  public function getAttachmentById(int $id): ?BroadcastAttachment;

  /**
   * @param int $broadcastId
   * @return BroadcastAttachment[]
   */
  public function getAttachmentsByBroadcastId(int $broadcastId): array;

  /**
   * @param string $token
   * @return null|BroadcastAttachment
   */
  public function getAttachmentByToken(string $token): ?BroadcastAttachment;

  /**
   * @param BroadcastAttachment $attachment
   * @return bool
   * @throws Exception
   */
  public function deleteAttachment(BroadcastAttachment $attachment): bool;

  /**
   * @param int $olderThanDay = 1
   * @return BroadcastAttachment[]
   */
  public function getAttachmentsOlderThanDayWithoutBroadcastId(int $olderThanDay = 1): array;

  /**
   * @return string
   */
  public function getUniqueRandomBroadcastAttachmentToken(): string;
}
