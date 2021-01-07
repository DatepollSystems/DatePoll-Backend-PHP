<?php

namespace App\Repositories\Broadcast\BroadcastDraft;

use App\Logging;
use App\Models\Broadcasts\BroadcastDraft;
use App\Repositories\User\User\IUserRepository;
use Exception;

class BroadcastDraftRepository implements IBroadcastDraftRepository {

  public function __construct(protected IUserRepository $userRepository) {
  }

  /**
   * @return BroadcastDraft[]
   */
  public function getAllBroadcastDraftsOrderedByDate(): array {
    return BroadcastDraft::orderBy('updated_at', 'DESC')
      ->get()->all();
  }

  /**
   * @param int $id
   * @return BroadcastDraft | null
   */
  public function getBroadcastDraftById(int $id): ?BroadcastDraft {
    return BroadcastDraft::find($id);
  }

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
  ): ?BroadcastDraft {
    if ($draft == null) {
      $draft = new BroadcastDraft([
        'subject' => $subject,
        'bodyHTML' => $bodyHTML,
        'body' => $body,
        'writer_user_id' => $writerId, ]);
    } else {
      $draft->subject = $subject;
      $draft->bodyHTML = $bodyHTML;
      $draft->body = $body;
    }

    if (! $draft->save()) {
      Logging::error('createOrUpdateBroadcastDraft', 'Draft failed to create or update! User id - ' . $writerId);

      return null;
    }

    return $draft;
  }

  /**
   * @param BroadcastDraft $draft
   * @return bool
   * @throws Exception
   */
  public function delete(BroadcastDraft $draft): bool {
    return $draft->delete();
  }
}
