<?php


namespace App\Repositories\Broadcast\Broadcast;

use App\Jobs\SendEmailJob;
use App\Logging;
use App\Mail\BroadcastMail;
use App\Models\Broadcasts\Broadcast;
use App\Models\Broadcasts\BroadcastForGroup;
use App\Models\Broadcasts\BroadcastForSubgroup;
use App\Models\Groups\Group;
use App\Models\Subgroups\Subgroup;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class BroadcastRepository implements IBroadcastRepository
{

  protected $userRepository = null;
  protected $settingRepository = null;

  public function __construct(IUserRepository $userRepository, ISettingRepository $settingRepository) {
    $this->userRepository = $userRepository;
    $this->settingRepository = $settingRepository;
  }

  /**
   * @return Broadcast[]|Collection
   */
  public function getAllBroadcasts() {
    return Broadcast::all();
  }

  /**
   * @return Broadcast[]|Collection
   */
  public function getAllBroadcastsOrderedByDate() {
    return Broadcast::orderBy('created_at', 'DESC')->get();
  }

  /**
   * @param Broadcast $broadcast
   * @return Broadcast|stdClass
   */
  public function getBroadcastAdminReturnable(Broadcast $broadcast) {
    $toReturnBroadcast = $this->getBroadcastCutReturnable($broadcast);
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
   * @return stdClass | Broadcast
   */
  private function getBroadcastCutReturnable(Broadcast $broadcast) {
    $toReturnBroadcast = new stdClass();
    $toReturnBroadcast->id = $broadcast->id;
    $toReturnBroadcast->subject = $broadcast->subject;
    $toReturnBroadcast->body = $broadcast->body;
    $toReturnBroadcast->writer_name = $broadcast->writer()->firstname . ' ' . $broadcast->writer()->surname;
    $toReturnBroadcast->writer_user_id = $broadcast->writer_user_id;
    $toReturnBroadcast->forEveryone = $broadcast->forEveryone;
    $toReturnBroadcast->created_at = $broadcast->created_at;
    $toReturnBroadcast->updated_at = $broadcast->updated_at;

    return $toReturnBroadcast;
  }

  /**
   * @param string $subject
   * @param string $bodyHTML
   * @param string $body
   * @param int $writerId
   * @param $groups
   * @param $subgroups
   * @param bool $forEveryone
   * @return Broadcast | null
   * @throws Exception
   */
  public function create(string $subject, string $bodyHTML, string $body, int $writerId, $groups, $subgroups, bool $forEveryone) {
    $broadcast = new Broadcast([
      'subject' => $subject,
      'bodyHTML' => $bodyHTML,
      'body' => $body,
      'writer_user_id' => $writerId,
      'forEveryone' => $forEveryone
    ]);

    if(!$broadcast->save()) {
      Logging::error('createBroadcast', 'Broadcast failed to create! User id - ' . $writerId);
      return null;
    }

    $users = array();

    if ($forEveryone) {
      $users = $this->userRepository->getAllUsers();
    } else {
      $userIds = array();

      foreach ($groups as $groupId) {
        $group = Group::find($groupId);

        if ($group == null) {
          Logging::error('createBroadcast', 'Broadcast failed to create! Unknown group_id - ' .
            $groupId . ' User id - ' . $writerId);
          $broadcast->delete();
          return null;
        }

        $broadcastForGroup = new BroadcastForGroup(['broadcast_id' => $broadcast->id, 'group_id' => $groupId]);
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
          Logging::error('createBroadcast', 'Broadcast failed to create! Unknown subgroup_id - ' .
            $subgroupId . ' User id - ' . $writerId);
          $broadcast->delete();
          return null;
        }

        $broadcastForSubgroup = new BroadcastForSubgroup(['broadcast_id' => $broadcast->id, 'subgroup_id' => $subgroupId]);
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

    $writer = $this->userRepository->getUserById($writerId);

    foreach ($users as $user) {
      if ($user->hasEmailAddresses() && $user->activated) {
        dispatch(new SendEmailJob(new BroadcastMail($subject, $body, $bodyHTML, $writer->firstname . " " . $writer->surname,
          $this->settingRepository), $user->getEmailAddresses()))->onQueue('default');
      }
    }

    return $broadcast;
  }


}