<?php

namespace App\Repositories\Broadcast\BroadcastAttachment;

use App\Models\Broadcasts\BroadcastAttachment;
use Exception;
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
   * @return BroadcastAttachment[]
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
