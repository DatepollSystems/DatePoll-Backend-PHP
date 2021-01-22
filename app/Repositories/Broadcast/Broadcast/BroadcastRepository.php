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
use App\Repositories\User\User\IUserRepository;
use App\Utils\ArrayHelper;
use App\Utils\EnvironmentHelper;
use App\Utils\QueueHelper;
use DateInterval;
use DateTime;
use Exception;

class BroadcastRepository implements IBroadcastRepository {

  public function __construct(
    protected ISettingRepository $settingRepository,
    protected IGroupRepository $groupRepository,
    protected IBroadcastAttachmentRepository $broadcastAttachmentRepository,
    protected IUserRepository $userRepository
  ) {
  }

  /**
   * @return Broadcast[]
   */
  public function getAllBroadcastsOrderedByDate(): array {
    return Broadcast::orderBy('created_at', 'DESC')
      ->get()->all();
  }

  /**
   * @param int $id
   * @return Broadcast | null
   */
  public function getBroadcastById(int $id): ?Broadcast {
    return Broadcast::find($id);
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
  ): ?Broadcast {
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
      $mAttachments .= '> <a href="' . $frontendUrl . '/download/' . $attachment->token . '">' . $attachment->name . '</a><br>';
    }

    if ($happened) {
      $mAttachments = '================= Anhänge =================<br>' . $mAttachments . '========================================<br>';
    }

    $writer = $this->userRepository->getUserById($writerId);
    if ($writer == null) {
      Logging::error(
        'createBroadcast',
        'Broadcast failed to create! Writer id is null');
      $broadcast->delete();

      return null;
    }
    $writerEmailAddress = null;
    if ($writer->hasEmailAddresses()) {
      $writerEmailAddress = $writer->getEmailAddresses()[0];
    }

    $time = new DateTime();
    if (EnvironmentHelper::isProduction()) {
      $time->add(new DateInterval('PT' . 2 . 'M'));
    }
    foreach ($users as $user) {
      if (! $user->information_denied && $user->activated && $user->hasEmailAddresses()) {
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
          $writer->getCompleteName(),
          $writerEmailAddress,
          $DatePollAddress,
          $mAttachments
        );
        $sendEmailJob = new SendBroadcastEmailJob($broadcastMail, $user->getEmailAddresses(), $user->id,
          $broadcast->id);

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
      $sendEmailJob = new SendBroadcastEmailJob($broadcastMail, $broadcastUserInfo->user()->getEmailAddresses(),
        $broadcast->id, $broadcastUserInfo->user()->id);

      QueueHelper::addDelayedJobToDefaultQueue($sendEmailJob, $time);
    }
  }

  /**
   * @param Broadcast $broadcast
   * @return bool
   * @throws Exception
   */
  public function delete(Broadcast $broadcast): bool {
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
  public function getBroadcastsForUserByIdOrderedByDate(int $userId, int $limit = -1): array {
    $query = BroadcastUserInfo::where('user_id', '=', $userId)
      ->orderBy('created_at', 'DESC');
    if ($limit != -1) {
      $query = $query->limit($limit);
    }

    return Broadcast::find(ArrayHelper::getPropertyArrayOfObjectArray($query->get()->all(), 'broadcast_id'))->all();

  }

  /**
   * @param int $userId
   * @param int $broadcastId
   * @return bool
   */
  public function isUserByIdAllowedToViewBroadcastById(int $userId, int $broadcastId): bool {
    return BroadcastUserInfo::where('user_id', '=', $userId)
        ->where('broadcast_id', '=', $broadcastId)
        ->orderBy('created_at', 'DESC')
        ->first() != null;
  }
}
