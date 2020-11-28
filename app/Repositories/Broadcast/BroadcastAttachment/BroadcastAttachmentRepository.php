<?php

namespace App\Repositories\Broadcast\BroadcastAttachment;

use App\Models\Broadcasts\BroadcastAttachment;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;

class BroadcastAttachmentRepository implements IBroadcastAttachmentRepository {

  /**
   * @param int $id
   * @return null|BroadcastAttachment
   */
  public function getAttachmentById(int $id) {
    return BroadcastAttachment::find($id);
  }

  /**
   * @param int $broadcastId
   * @return BroadcastAttachment[]|Collection<BroadcastAttachment>
   */
  public function getAttachmentsByBroadcastId(int $broadcastId) {
    return BroadcastAttachment::where('broadcast_id', '=', $broadcastId)->get();
  }

  /**
   * @param string $token
   * @return null|BroadcastAttachment
   */
  public function getAttachmentByToken(string $token) {
    return BroadcastAttachment::where('token', '=', $token)->first();
  }

  /**
   * @param BroadcastAttachment $attachment
   * @return bool|null
   * @throws Exception
   */
  public function deleteAttachment(BroadcastAttachment $attachment) {
    Storage::delete($attachment->path);

    return $attachment->delete();
  }

  /**
   * @param int $olderThanDay = 1
   * @return BroadcastAttachment[]
   */
  public function getAttachmentsOlderThanDayWithoutBroadcastId(int $olderThanDay = 1): array {
    $rAttachments = [];
    $attachments = BroadcastAttachment::where('broadcast_id', '=', null)->get();
    foreach ($attachments as $attachment) {
      if (strtotime('-' . $olderThanDay . ' day') > strtotime($attachment->created_at)) {
        $rAttachments[] = $attachment;
      }
    }

    return $rAttachments;
  }

  /**
   * @return string
   */
  public function getUniqueRandomBroadcastAttachmentToken(): string {
    while (true) {
      $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
      $charactersLength = strlen($characters);
      $randomToken = '';
      for ($i = 0; $i < 15; $i++) {
        $randomToken .= $characters[rand(0, $charactersLength - 1)];
      }

      if (BroadcastAttachment::where('token', $randomToken)
        ->first() == null) {
        break;
      }
    }

    return $randomToken;
  }
}
