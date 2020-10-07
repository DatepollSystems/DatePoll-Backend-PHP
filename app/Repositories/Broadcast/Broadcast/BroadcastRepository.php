<?php


namespace App\Repositories\Broadcast\Broadcast;

use App\Jobs\SendEmailJob;
use App\Logging;
use App\Mail\BroadcastMail;
use App\Models\Broadcasts\Broadcast;
use App\Models\Broadcasts\BroadcastForGroup;
use App\Models\Broadcasts\BroadcastForSubgroup;
use App\Models\Broadcasts\BroadcastUserInfo;
use App\Models\Subgroups\Subgroup;
use App\Models\User\User;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\System\Setting\ISettingRepository;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Queue;
use stdClass;

class BroadcastRepository implements IBroadcastRepository
{

  protected $settingRepository = null;
  protected $groupRepository = null;

  public function __construct(ISettingRepository $settingRepository, IGroupRepository $groupRepository) {
    $this->settingRepository = $settingRepository;
    $this->groupRepository = $groupRepository;
  }

  /**
   * @return Broadcast[]|Collection<Broadcast[]>
   */
  public function getAllBroadcastsOrderedByDate() {
    return Broadcast::orderBy('created_at', 'DESC')
                    ->get();
  }

  /**
   * @param int $id
   * @return Broadcast | null
   */
  public function getBroadcastById(int $id) {
    return Broadcast::find($id);
  }

  /**
   * @param Broadcast $broadcast
   * @return stdClass | Broadcast
   */
  public function getBroadcastUserReturnable(Broadcast $broadcast) {
    $toReturnBroadcast = $this->getBroadcastReturnable($broadcast);
    $toReturnBroadcast->bodyHTML = $broadcast->bodyHTML;

    return $toReturnBroadcast;
  }

  /**
   * @param Broadcast $broadcast
   * @return stdClass | Broadcast
   */
  public function getBroadcastReturnable(Broadcast $broadcast) {
    $toReturnBroadcast = new stdClass();
    $toReturnBroadcast->id = $broadcast->id;
    $toReturnBroadcast->subject = $broadcast->subject;
    $toReturnBroadcast->body = $broadcast->body;
    $toReturnBroadcast->writer_name = $broadcast->writer()->getName();
    $toReturnBroadcast->writer_user_id = $broadcast->writer_user_id;
    $toReturnBroadcast->for_everyone = $broadcast->forEveryone;
    $toReturnBroadcast->created_at = $broadcast->created_at;
    $toReturnBroadcast->updated_at = $broadcast->updated_at;

    $toReturnGroups = array();
    $groups = $broadcast->broadcastsForGroups();
    foreach ($groups as $group) {
      $group = $group->group();
      $toReturnGroup = new stdClass();
      $toReturnGroup->id = $group->id;
      $toReturnGroup->name = $group->name;
      $toReturnGroups[] = $toReturnGroup;
    }
    $toReturnBroadcast->groups = $toReturnGroups;

    $toReturnSubgroups = array();
    $subgroups = $broadcast->broadcastsForSubgroups();
    foreach ($subgroups as $subgroup) {
      $subgroup = $subgroup->subgroup();
      $toReturnSubgroup = new stdClass();
      $toReturnSubgroup->id = $subgroup->id;
      $toReturnSubgroup->name = $subgroup->name;
      $toReturnSubgroup->group_id = $subgroup->group_id;
      $toReturnSubgroup->group_name = $subgroup->group()->name;
      $toReturnSubgroups[] = $toReturnSubgroup;
    }
    $toReturnBroadcast->subgroups = $toReturnSubgroups;

    return $toReturnBroadcast;
  }

  /**
   * @param Broadcast $broadcast
   * @return Broadcast|stdClass
   */
  public function getBroadcastSentReceiptReturnable(Broadcast $broadcast) {
    $toReturnBroadcast = $this->getBroadcastReturnable($broadcast);

    $toReturnBroadcast->bodyHTML = $broadcast->bodyHTML;

    $userInfos = [];
    foreach (BroadcastUserInfo::where('broadcast_id', '=', $broadcast->id)
                              ->orderBy('sent')
                              ->get() as $userInfo) {
      $userInfoDTO = new stdClass();
      $userInfoDTO->id = $userInfo->id;
      $userInfoDTO->broadcast_id = $userInfo->broadcast_id;
      $userInfoDTO->user_id = $userInfo->user_id;
      $userInfoDTO->user_name = $userInfo->user()->firstname . ' ' . $userInfo->user()->surname;
      $userInfoDTO->sent = $userInfo->sent;
      $userInfoDTO->created_at = $userInfo->created_at;
      $userInfoDTO->updated_at = $userInfo->updated_at;

      $userInfos[] = $userInfoDTO;
    }

    $toReturnBroadcast->users_info = $userInfos;

    return $toReturnBroadcast;
  }

  /**
   * @param string $subject
   * @param string $bodyHTML
   * @param string $body
   * @param int $writerId
   * @param int[] $groups
   * @param int[] $subgroups
   * @param bool $forEveryone
   * @return Broadcast | null
   * @throws Exception
   */
  public function create(string $subject, string $bodyHTML, string $body, int $writerId, $groups, $subgroups,
                         bool $forEveryone) {
    $broadcast = new Broadcast([
      'subject' => $subject,
      'bodyHTML' => $bodyHTML,
      'body' => $body,
      'writer_user_id' => $writerId,
      'forEveryone' => $forEveryone]);

    if (!$broadcast->save()) {
      Logging::error('createBroadcast', 'Broadcast failed to create! User id - ' . $writerId);
      return null;
    }

    $users = array();

    if ($forEveryone) {
      $users = User::all();
    } else {
      $userIds = array();

      foreach ($groups as $groupId) {
        $group = $this->groupRepository->getGroupById($groupId);

        if ($group == null) {
          Logging::error('createBroadcast',
            'Broadcast failed to create! Unknown group_id - ' . $groupId . ' User id - ' . $writerId);
          $broadcast->delete();
          return null;
        }

        $broadcastForGroup = new BroadcastForGroup([
          'broadcast_id' => $broadcast->id,
          'group_id' => $groupId]);
        if (!$broadcastForGroup->save()) {
          Logging::error('createBroadcast', 'Broadcast failed to create! - BroadcastForGroup');
          $broadcast->delete();
          return null;
        }

        foreach ($group->usersMemberOfGroups() as $memberOfGroup) {
          $user = $memberOfGroup->user();
          $users[] = $user;
          $userIds[] = $user->id;
        }
      }

      foreach ($subgroups as $subgroupId) {
        $subgroup = Subgroup::find($subgroupId);

        if ($subgroup == null) {
          Logging::error('createBroadcast',
            'Broadcast failed to create! Unknown subgroup_id - ' . $subgroupId . ' User id - ' . $writerId);
          $broadcast->delete();
          return null;
        }

        $broadcastForSubgroup = new BroadcastForSubgroup([
          'broadcast_id' => $broadcast->id,
          'subgroup_id' => $subgroupId]);
        if (!$broadcastForSubgroup->save()) {
          Logging::error('createBroadcast', 'Broadcast failed to create! - BroadcastForSubgroup');
          $broadcast->delete();
          return null;
        }

        foreach ($subgroup->usersMemberOfSubgroups() as $memberOfGroup) {
          $user = $memberOfGroup->user();

          if (!in_array($user->id, $userIds, true)) {
            $userIds[] = $user->id;
            $users[] = $user;
          }
        }
      }
    }

    $writer = User::find($writerId);
    $writerEmailAddress = null;
    if ($writer->hasEmailAddresses()) {
      $writerEmailAddress = $writer->getEmailAddresses()[0];
    }


    $time = new DateTime();
    foreach ($users as $user) {
      if ($user->hasEmailAddresses() && $user->activated && !$user->information_denied) {
        $broadcastUserInfo = new BroadcastUserInfo([
          'broadcast_id' => $broadcast->id,
          'user_id' => $user->id,
          'sent' => false]);
        if (!$broadcastUserInfo->save()) {
          Logging::error('createBroadcast', 'Broadcast failed to create! - BroadcastUserInfo | User id - ' . $user->id);
          $broadcast->delete();
          return null;
        }

        $time->add(new DateInterval('PT' . 1 . 'M'));
        $broadcastMail = new BroadcastMail($subject, $body, $bodyHTML, $writer->firstname . " " . $writer->surname,
          $writerEmailAddress, $this->settingRepository);
        $sendEmailJob = new SendEmailJob($broadcastMail, $user->getEmailAddresses());
        $sendEmailJob->broadcastId = $broadcast->id;
        $sendEmailJob->userId = $user->id;

        Queue::later($time, $sendEmailJob, null, "default");
      }
    }

    return $broadcast;
  }


  /**
   * @param Broadcast $broadcast
   * @throws Exception
   */
  public function reQueueNotSentBroadcastsForBroadcast(Broadcast $broadcast) {
    $broadcastUserInfos = BroadcastUserInfo::where('broadcast_id', '=', $broadcast->id)
                                           ->where('sent', '=', false)
                                           ->get();

    $time = new DateTime();

    $writerEmailAddress = null;
    if ($broadcast->writer()->hasEmailAddresses()) {
      $writerEmailAddress = $broadcast->writer()->getEmailAddresses()[0];
    }
    $writerName = $broadcast->writer()->getName();

    foreach ($broadcastUserInfos as $broadcastUserInfo) {
      $time->add(new DateInterval('PT' . 1 . 'M'));
      $broadcastMail = new BroadcastMail($broadcast->subject, $broadcast->body, $broadcast->bodyHTML,
        $writerName, $writerEmailAddress, $this->settingRepository);
      $sendEmailJob = new SendEmailJob($broadcastMail, $broadcastUserInfo->user()->getEmailAddresses());
      $sendEmailJob->broadcastId = $broadcast->id;
      $sendEmailJob->userId = $broadcastUserInfo->user()->id;

      Queue::later($time, $sendEmailJob, null, "default");
    }
  }


  /**
   * @param Broadcast $broadcast
   * @return bool|null
   * @throws Exception
   */
  public function delete(Broadcast $broadcast) {
    return $broadcast->delete();
  }

  /**
   * @param int $userId
   * @param int $limit
   * @return Broadcast[]
   */
  public function getBroadcastsForUserByIdOrderedByDate(int $userId, int $limit = -1) {
    if ($limit != -1) {
      $broadcastUserInfos = BroadcastUserInfo::where('user_id', '=', $userId)
                                             ->orderBy('created_at', 'DESC')
                                             ->limit($limit)
                                             ->get();
    } else {
      $broadcastUserInfos = BroadcastUserInfo::where('user_id', '=', $userId)
                                             ->orderBy('created_at', 'DESC')
                                             ->get();
    }

    $broadcasts = array();
    foreach ($broadcastUserInfos as $broadcastUserInfo) {
      $broadcasts[] = $broadcastUserInfo->broadcast();
    }

    return $broadcasts;
  }

  /**
   * @param int $userId
   * @param int $broadcastId
   * @return bool
   */
  public function isUserByIdAllowedToViewBroadcastById(int $userId, int $broadcastId) {
    $broadcastUserInfo = BroadcastUserInfo::where('user_id', '=', $userId)
                                          ->where('broadcast_id', '=', $broadcastId)
                                          ->orderBy('created_at', 'DESC')
                                          ->first();
    return $broadcastUserInfo != null;
  }
}
