<?php

namespace App\Repositories\Broadcast\BroadcastAttachment;

use App\Models\Broadcasts\BroadcastAttachment;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IBroadcastAttachmentRepository {

  /**
   * @param int $id
   * @return null|BroadcastAttachment
   */
  public function getAttachmentById(int $id);

  /**
   * @param int $broadcastId
   * @return BroadcastAttachment[]|Collection<BroadcastAttachment>
   */
  public function getAttachmentsByBroadcastId(int $broadcastId);

  /**
   * @param string $token
   * @return null|BroadcastAttachment
   */
  public function getAttachmentByToken(string $token);

  /**
   * @param BroadcastAttachment $attachment
   * @return bool|null
   * @throws Exception
   */
  public function deleteAttachment(BroadcastAttachment $attachment);

  /**
   * @return string
   */
  public function getUniqueRandomBroadcastAttachmentToken(): string;
}
