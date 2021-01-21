<?php

namespace App\Repositories\Broadcast\BroadcastAttachment;

use App\Models\Broadcasts\BroadcastAttachment;
use App\Utils\Generator;
use Exception;
use Illuminate\Support\Facades\Storage;

class BroadcastAttachmentRepository implements IBroadcastAttachmentRepository {

  /**
   * @param int $id
   * @return null|BroadcastAttachment
   */
  public function getAttachmentById(int $id): ?BroadcastAttachment {
    return BroadcastAttachment::find($id);
  }

  /**
   * @param int $broadcastId
   * @return BroadcastAttachment[]
   */
  public function getAttachmentsByBroadcastId(int $broadcastId): array {
    return BroadcastAttachment::where('broadcast_id', '=', $broadcastId)->get()->all();
  }

  /**
   * @param string $token
   * @return null|BroadcastAttachment
   */
  public function getAttachmentByToken(string $token): ?BroadcastAttachment {
    return BroadcastAttachment::where('token', '=', $token)->first();
  }

  /**
   * @param BroadcastAttachment $attachment
   * @return bool
   * @throws Exception
   */
  public function deleteAttachment(BroadcastAttachment $attachment): bool {
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
      $randomToken = Generator::getRandomMixedNumberAndABCToken(15);

      if (BroadcastAttachment::where('token', $randomToken)
        ->first() == null) {
        break;
      }
    }

    return $randomToken;
  }
}
