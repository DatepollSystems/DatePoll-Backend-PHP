<?php

namespace App\Repositories\Broadcast\Broadcast;

use App\Jobs\SendBroadcastEmailJob;
use App\Logging;
use App\Mail\BroadcastMail;
use App\Models\Broadcasts\Broadcast;
use App\Models\Broadcasts\BroadcastForGroup;
use App\Models\Broadcasts\BroadcastForSubgroup;
use App\Models\Broadcasts\BroadcastUserInfo;
use App\Models\Subgroups\Subgroup;
use App\Models\User\User;
use App\Repositories\Broadcast\BroadcastAttachment\IBroadcastAttachmentRepository;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Utils\QueueHelper;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class BroadcastRepository implements IBroadcastRepository {
  protected ISettingRepository $settingRepository;
  protected IGroupRepository $groupRepository;
  protected IBroadcastAttachmentRepository $broadcastAttachmentRepository;

  public function __construct(
    ISettingRepository $settingRepository,
    IGroupRepository $groupRepository,
    IBroadcastAttachmentRepository $broadcastAttachmentRepository
  ) {
    $this->settingRepository = $settingRepository;
    $this->groupRepository = $groupRepository;
    $this->broadcastAttachmentRepository = $broadcastAttachmentRepository;
  }

  /**
   * @return Broadcast[]|Collection<Broadcast>
   */
  public function getAllBroadcastsOrderedByDate() {
    return Broadcast::orderBy('created_at', 'DESC')
      ->get();
  }

  /**
   * @param int $id
   * @return Broadcast | null
   */
  public function getBroadcastById(int $id): ?Broadcast {
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
    $toReturnBroadcast->writer_name = $broadcast->writer()->getCompleteName();
    $toReturnBroadcast->writer_user_id = $broadcast->writer_user_id;
    $toReturnBroadcast->for_everyone = $broadcast->forEveryone;
    $toReturnBroadcast->created_at = $broadcast->created_at;
    $toReturnBroadcast->updated_at = $broadcast->updated_at;

    $toReturnGroups = [];
    $groups = $broadcast->broadcastsForGroups();
    foreach ($groups as $group) {
      $group = $group->group();
      $toReturnGroup = new stdClass();
      $toReturnGroup->id = $group->id;
      $toReturnGroup->name = $group->name;
      $toReturnGroups[] = $toReturnGroup;
    }
    $toReturnBroadcast->groups = $toReturnGroups;

    $toReturnSubgroups = [];
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

    $toReturnAttachments = [];
    $attachments = $broadcast->attachments();
    foreach ($attachments as $attachment) {
      $toReturnAttachments[] = $attachment;
    }
    $toReturnBroadcast->attachments = $toReturnAttachments;

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
   * @param array $attachments
   * @return Broadcast | null
   * @throws Exception
   */
  public function create(
    string $subject,
    string $bodyHTML,
    string $body,
    int $writerId,
    array $groups,
    array $subgroups,
    bool $forEveryone,
    array $attachments
  ) {
    $broadcast = new Broadcast([
      'subject' => $subject,
      'bodyHTML' => $bodyHTML,
      'body' => $body,
      'writer_user_id' => $writerId,
      'forEveryone' => $forEveryone,]);

    if (! $broadcast->save()) {
      Logging::error('createBroadcast', 'Broadcast failed to create! User id - ' . $writerId);

      return null;
    }

    $users = [];

    if ($forEveryone) {
      $users = User::all();
    } else {
      $userIds = [];

      foreach ($groups as $groupId) {
        $group = $this->groupRepository->getGroupById($groupId);

        if ($group == null) {
          Logging::error(
            'createBroadcast',
            'Broadcast failed to create! Unknown group_id - ' . $groupId . ' User id - ' . $writerId
          );
          $broadcast->delete();

          return null;
        }

        $broadcastForGroup = new BroadcastForGroup([
          'broadcast_id' => $broadcast->id,
          'group_id' => $groupId,]);
        if (! $broadcastForGroup->save()) {
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
          Logging::error(
            'createBroadcast',
            'Broadcast failed to create! Unknown subgroup_id - ' . $subgroupId . ' User id - ' . $writerId
          );
          $broadcast->delete();

          return null;
        }

        $broadcastForSubgroup = new BroadcastForSubgroup([
          'broadcast_id' => $broadcast->id,
          'subgroup_id' => $subgroupId,]);
        if (! $broadcastForSubgroup->save()) {
          Logging::error('createBroadcast', 'Broadcast failed to create! - BroadcastForSubgroup');
          $broadcast->delete();

          return null;
        }

        foreach ($subgroup->usersMemberOfSubgroups() as $memberOfGroup) {
          $user = $memberOfGroup->user();

          if (! in_array($user->id, $userIds, true)) {
            $userIds[] = $user->id;
            $users[] = $user;
          }
        }
      }
    }

    $DatePollAddress = $this->settingRepository->getUrl();
    $frontendUrl = $this->settingRepository->getUrl();

    $mAttachments = '';
    $happened = false;
    foreach ($attachments as $attachmentId) {
      $attachment = $this->broadcastAttachmentRepository->getAttachmentById((int)$attachmentId);

      if ($attachment == null) {
        Logging::error(
          'createBroadcast',
          'Broadcast failed to create! Unknown attachment_id - ' . $attachmentId . ' User id - ' . $writerId
        );
        $broadcast->delete();

        return null;
      }
      if ($attachment->broadcast_id != null) {
        Logging::error(
          'createBroadcast',
          'Broadcast failed to create! Attachment already used. attachment_id - ' . $attachmentId . ' User id - ' . $writerId . ' Broadcast id - ' . $attachment->broadcast_id
        );
        $broadcast->delete();

        return null;
      }

      $happened = true;

      $attachment->broadcast_id = $broadcast->id;
      $attachment->save();
      $mAttachments = $mAttachments . '> <a href="' . $frontendUrl . '/download/' . $attachment->token . '">' . $attachment->name . '</a><br>';
    }

    if ($happened) {
      $mAttachments = '================= Anhänge =================<br>' . $mAttachments . '========================================<br>';
    }

    $writer = User::find($writerId);
    $writerEmailAddress = null;
    if ($writer->hasEmailAddresses()) {
      $writerEmailAddress = $writer->getEmailAddresses()[0];
    }

    $time = new DateTime();
    foreach ($users as $user) {
      if ($user->hasEmailAddresses() && $user->activated && ! $user->information_denied) {
        $broadcastUserInfo = new BroadcastUserInfo([
          'broadcast_id' => $broadcast->id,
          'user_id' => $user->id,
          'sent' => false,]);
        if (! $broadcastUserInfo->save()) {
          Logging::error('createBroadcast', 'Broadcast failed to create! - BroadcastUserInfo | User id - ' . $user->id);
          $broadcast->delete();

          return null;
        }

        $time->add(new DateInterval('PT' . 1 . 'M'));
        $broadcastMail = new BroadcastMail(
          $subject,
          $body,
          $bodyHTML,
          $writer->getName(),
          $writerEmailAddress,
          $DatePollAddress,
          $mAttachments
        );
        $sendEmailJob = new SendBroadcastEmailJob($broadcastMail, $user->getEmailAddresses(), $user->id, $broadcast->id);

        QueueHelper::addDelayedJobToDefaultQueue($sendEmailJob, $time);
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

    $DatePollAddress = $this->settingRepository->getUrl();
    $frontendUrl = $this->settingRepository->getUrl();

    $mAttachments = '';
    $happened = false;
    foreach ($this->broadcastAttachmentRepository->getAttachmentsByBroadcastId($broadcast->id) as $attachment) {
      $happened = true;

      $attachment->broadcast_id = $broadcast->id;
      $attachment->save();
      $mAttachments = $mAttachments . '> <a href="' . $frontendUrl . '/download/' . $attachment->token . '">' . $attachment->name . '</a><br>';
    }

    if ($happened) {
      $mAttachments = '================= Anhänge =================<br>' . $mAttachments . '========================================<br>';
    }

    $time = new DateTime();

    $writerEmailAddress = null;
    if ($broadcast->writer()->hasEmailAddresses()) {
      $writerEmailAddress = $broadcast->writer()->getEmailAddresses()[0];
    }
    $writerName = $broadcast->writer()->getCompleteName();

    foreach ($broadcastUserInfos as $broadcastUserInfo) {
      $time->add(new DateInterval('PT' . 1 . 'M'));
      $broadcastMail = new BroadcastMail(
        $broadcast->subject,
        $broadcast->body,
        $broadcast->bodyHTML,
        $writerName,
        $writerEmailAddress,
        $DatePollAddress,
        $mAttachments
      );
      $sendEmailJob = new SendBroadcastEmailJob($broadcastMail, $broadcastUserInfo->user()->getEmailAddresses(), $broadcast->id, $broadcastUserInfo->user()->id);

      QueueHelper::addDelayedJobToDefaultQueue($sendEmailJob, $time);
    }
  }

  /**
   * @param Broadcast $broadcast
   * @return bool|null
   * @throws Exception
   */
  public function delete(Broadcast $broadcast) {
    $broadcastAttachments = $broadcast->attachments();
    foreach ($broadcastAttachments as $attachment) {
      if (! $this->broadcastAttachmentRepository->deleteAttachment($attachment)) {
        Logging::error('deleteAttachment of delete Broadcast', 'Could not delete attachment');

        return false;
      }
    }

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

    $broadcasts = [];
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
