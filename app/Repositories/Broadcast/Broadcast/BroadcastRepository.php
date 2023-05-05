<?php

namespace App\Repositories\Broadcast\Broadcast;

use App\Jobs\SendBroadcastEmailJob;
use App\Logging;
use App\Mail\BroadcastMail;
use App\Models\Broadcasts\Broadcast;
use App\Models\Broadcasts\BroadcastForGroup;
use App\Models\Broadcasts\BroadcastForSubgroup;
use App\Models\Broadcasts\BroadcastUserInfo;
use App\Models\User\User;
use App\Repositories\Broadcast\BroadcastAttachment\IBroadcastAttachmentRepository;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\Group\Subgroup\ISubgroupRepository;
use App\Repositories\Interfaces\AHasYearsRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserSetting\UserSettingRepository;
use App\Utils\ArrayHelper;
use App\Utils\DateHelper;
use App\Utils\EnvironmentHelper;
use App\Utils\QueueHelper;
use Exception;
use Illuminate\Support\Facades\DB;
use JetBrains\PhpStorm\Pure;

class BroadcastRepository extends AHasYearsRepository implements IBroadcastRepository {
  #[Pure]
  public function __construct(
    protected ISettingRepository $settingRepository,
    protected IGroupRepository $groupRepository,
    protected ISubgroupRepository $subgroupRepository,
    protected IBroadcastAttachmentRepository $broadcastAttachmentRepository,
    protected IUserRepository $userRepository,
    protected UserSettingRepository $userSettingRepository
  ) {
    parent::__construct('broadcasts', 'created_at');
  }

  /**
   * @param int|null $year
   * @return Broadcast[]
   */
  public function getDataOrderedByDate(int $year = null): array {
    if ($year != null) {
      return Broadcast::whereYear('created_at', '=', $year)->orderBy('created_at', 'DESC')->get()->all();
    }

    return Broadcast::orderBy('created_at', 'DESC')->get()->all();
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
          if (ArrayHelper::notInArray($userIds, $memberOfGroup->user_id)) {
            $user = $memberOfGroup->user;
            $users[] = $user;
            $userIds[] = $user->id;
          }
        }
      }

      foreach ($subgroups as $subgroupId) {
        $subgroup = $this->subgroupRepository->getSubgroupById($subgroupId);

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

        foreach ($subgroup->usersMemberOfSubgroups() as $memberOfSubgroup) {
          if (ArrayHelper::notInArray($userIds, $memberOfSubgroup->user_id)) {
            $user = $memberOfSubgroup->user;
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
        'Broadcast failed to create! Writer id is null'
      );
      $broadcast->delete();

      return null;
    }
    $writerEmailAddress = null;
    if ($writer->hasEmailAddresses()) {
      $writerEmailAddress = $writer->getEmailAddresses()[0];
    }

    $time = DateHelper::getCurrentDateTime();
    if (EnvironmentHelper::isProduction()) {
      $time = DateHelper::addMinuteToDateTime($time, 3);
    }
    foreach ($users as $user) {
      if (! $user->information_denied && $user->activated && $user->hasEmailAddresses() && $this->userSettingRepository->getNotifyMeBroadcastEmailsForUser($user->id)) {
        $broadcastUserInfo = new BroadcastUserInfo([
          'broadcast_id' => $broadcast->id,
          'user_id' => $user->id,
          'sent' => false,]);
        if (! $broadcastUserInfo->save()) {
          Logging::error('createBroadcast', 'Broadcast failed to create! - BroadcastUserInfo | User id - ' . $user->id);
          $broadcast->delete();

          return null;
        }

        $time = DateHelper::addMinuteToDateTime($time, 1);
        $broadcastMail = new BroadcastMail(
          $subject,
          $body,
          $bodyHTML,
          $writer->getCompleteName(),
          $writerEmailAddress,
          $DatePollAddress,
          $mAttachments
        );
        $sendEmailJob = new SendBroadcastEmailJob(
          $broadcastMail,
          $user->getEmailAddresses(),
          $user->id,
          $broadcast->id
        );

        QueueHelper::addDelayedJobToDefaultQueue($sendEmailJob, $time);
      }
    }

    return $broadcast;
  }

  /**
   * @param Broadcast $broadcast
   * @throws Exception
   */
  public function reQueueNotSentBroadcastsForBroadcast(Broadcast $broadcast): void {
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
      $mAttachments .= '> <a href="' . $frontendUrl . '/download/' . $attachment->token . '">' . $attachment->name . '</a><br>';
    }

    if ($happened) {
      $mAttachments = '================= Anhänge =================<br>' . $mAttachments . '========================================<br>';
    }

    $time = DateHelper::getCurrentDateTime();

    $writerEmailAddress = null;
    if ($broadcast->writer()->hasEmailAddresses()) {
      $writerEmailAddress = $broadcast->writer()->getEmailAddresses()[0];
    }
    $writerName = $broadcast->writer()->getCompleteName();

    foreach ($broadcastUserInfos as $broadcastUserInfo) {
      Logging::info('reQueueNotSentBroadcasts', 'Queuing "' . $broadcastUserInfo->user()->getCompleteName() . '"');

      $time = DateHelper::addMinuteToDateTime($time, 1);
      $broadcastMail = new BroadcastMail(
        $broadcast->subject,
        $broadcast->body,
        $broadcast->bodyHTML,
        $writerName,
        $writerEmailAddress,
        $DatePollAddress,
        $mAttachments
      );
      $sendEmailJob = new SendBroadcastEmailJob(
        $broadcastMail,
        $broadcastUserInfo->user()->getEmailAddresses(),
        $broadcastUserInfo->user()->id,
        $broadcast->id,
      );

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
   * @param int|null $limit
   * @param int|null $page
   * @return Broadcast[]
   */
  public function getBroadcastsForUserOrderedByDate(int $userId, ?int $limit = null, ?int $page = null): array {
    $query = Broadcast::orderBy('created_at', 'DESC');
    if ($page != null && $limit != null) {
      $query = $query->skip($page * $limit)->take($limit);
    }

    $broadcasts = [];
    foreach ($query->get()->all() as $broadcast) {
      if ($this->isUserAllowedToViewBroadcast($userId, $broadcast)) {
        $broadcasts[] = $broadcast;
      }
    }

    if ($limit != null && $page == null) {
      $broadcasts = ArrayHelper::getFirstValuesOfArray($broadcasts, $limit);
    }

    return $broadcasts;
  }

  /**
   * @param string $search
   * @return array
   */
  public function searchBroadcasts(string $search): array {
    $search = '%' . $search . '%';

    return Broadcast::find(ArrayHelper::getPropertyArrayOfObjectArray(DB::table('broadcasts')
      ->join('users', 'users.id', '=', 'broadcasts.writer_user_id')
      ->where(DB::raw('concat(users.firstname, " ", users.surname)'), 'like', $search)
      ->orWhere('subject', 'like', $search)
      ->orWhere('body', 'like', $search)
      ->select('broadcasts.id as id')->get()->all(), 'id'))->all();
  }

  /**
   * @param int $userId
   * @param Broadcast $broadcast
   * @return bool
   */
  public function isUserAllowedToViewBroadcast(int $userId, Broadcast $broadcast): bool {
    $inGroup = true;
    $inSubgroup = true;
    if (! $broadcast->forEveryone) {
      $inGroup = DB::table('broadcasts_for_groups')->join(
        'users_member_of_groups',
        'broadcasts_for_groups.group_id',
        '=',
        'users_member_of_groups.group_id'
      )->where(
        'broadcasts_for_groups.broadcast_id',
        '=',
        $broadcast->id
      )->where('users_member_of_groups.user_id', '=', $userId)->count() > 0;

      $inSubgroup = DB::table('broadcasts_for_subgroups')->join(
        'users_member_of_subgroups',
        'broadcasts_for_subgroups.subgroup_id',
        '=',
        'users_member_of_subgroups.subgroup_id'
      )->where(
        'broadcasts_for_subgroups.broadcast_id',
        '=',
        $broadcast->id
      )->where('users_member_of_subgroups.user_id', '=', $userId)->count() > 0;
    }

    return $broadcast->forEveryone || $inGroup || $inSubgroup;
  }
}
