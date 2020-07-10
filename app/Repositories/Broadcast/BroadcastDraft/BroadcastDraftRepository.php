<?php


namespace App\Repositories\Broadcast\BroadcastDraft;

use App\Jobs\SendEmailJob;
use App\Logging;
use App\Mail\BroadcastMail;
use App\Models\Broadcasts\Broadcast;
use App\Models\Broadcasts\BroadcastDraft;
use App\Models\Broadcasts\BroadcastForGroup;
use App\Models\Broadcasts\BroadcastForSubgroup;
use App\Models\Broadcasts\BroadcastUserInfo;
use App\Models\Groups\Group;
use App\Models\Subgroups\Subgroup;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class BroadcastDraftRepository implements IBroadcastDraftRepository
{

  protected $userRepository = null;

  public function __construct(IUserRepository $userRepository) {
    $this->userRepository = $userRepository;
  }

  /**
   * @return BroadcastDraft[]|Collection
   */
  public function getAllBroadcastDraftsOrderedByDate() {
    return BroadcastDraft::orderBy('updated_at', 'DESC')
                         ->get();
  }

  /**
   * @param int $id
   * @return BroadcastDraft | null
   */
  public function getBroadcastDraftById(int $id) {
    return BroadcastDraft::find($id);
  }

  /**
   * @param BroadcastDraft $draft
   * @return stdClass
   */
  public function getBroadcastDraftReturnable(BroadcastDraft $draft): stdClass {
    $toReturnDraft = new stdClass();
    $toReturnDraft->id = $draft->id;
    $toReturnDraft->subject = $draft->subject;
    $toReturnDraft->body = $draft->body;
    $toReturnDraft->bodyHTML = $draft->bodyHTML;
    $toReturnDraft->writer_name = $draft->writer()->firstname . ' ' . $draft->writer()->surname;
    $toReturnDraft->writer_user_id = $draft->writer_user_id;
    $toReturnDraft->created_at = $draft->created_at;
    $toReturnDraft->updated_at = $draft->updated_at;

    return $toReturnDraft;
  }

  /**
   * @param string $subject
   * @param string $bodyHTML
   * @param string $body
   * @param int $writerId
   * @param BroadcastDraft|null $draft
   * @return BroadcastDraft | null
   */
  public function createOrUpdate(string $subject, string $bodyHTML, string $body, int $writerId, BroadcastDraft $draft = null) {
    if ($draft == null) {
      $draft = new BroadcastDraft([
        'subject' => $subject,
        'bodyHTML' => $bodyHTML,
        'body' => $body,
        'writer_user_id' => $writerId]);
    } else {
      $draft->subject = $subject;
      $draft->bodyHTML = $bodyHTML;
      $draft->body = $body;
    }

    if (!$draft->save()) {
      Logging::error('createOrUpdateBroadcastDraft', 'Draft failed to create or update! User id - ' . $writerId);
      return null;
    }
    return $draft;
  }


  /**
   * @param BroadcastDraft $draft
   * @return bool|null
   */
  public function delete(BroadcastDraft $draft) {
    return $draft->delete();
  }
}