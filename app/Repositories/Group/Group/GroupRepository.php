<?php

namespace App\Repositories\Group\Group;

use App\Logging;
use App\Models\Groups\Group;
use App\Models\Groups\UsersMemberOfGroups;
use App\Models\User\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

class GroupRepository implements IGroupRepository {
  /**
   * @return Group[]|Collection
   */
  public function getAllGroups() {
    return Group::all();
  }

  /**
   * @return Group[]|Collection
   */
  public function getAllGroupsOrdered() {
    return Group::orderBy('orderN')
      ->get();
  }

  /**
   * @return Group[]|Collection
   */
  public function getAllGroupsWithSubgroupsOrdered() {
    $groups = $this->getAllGroupsOrdered();

    foreach ($groups as $group) {
      $group->subgroups = $group->getSubgroupsOrdered();
    }

    return $groups;
  }

  /**
   * @param int $id
   * @return Group|null
   */
  public function getGroupById(int $id) {
    return Group::find($id);
  }

  /**
   * @param string $name
   * @param string $description
   * @param int|null $orderN
   * @param Group|null $group
   * @return Group|null
   */
  public function createOrUpdateGroup(string $name, string $description, $orderN = null, $group = null) {
    if ($group == null) {
      $group = new Group([
        'name' => $name,
        'orderN' => $orderN,
        'description' => $description, ]);
    } else {
      $group->name = $name;
      $group->description = $description;
      $group->orderN = $orderN;
      if ($orderN == null) {
        $group->orderN = 0;
      }
    }

    if (! $group->save()) {
      Logging::error('createOrUpdateGroup', 'Could not update group');

      return null;
    }

    return $group;
  }

  /**
   * @param Group $group
   * @return boolean
   * @throws Exception
   */
  public function delete($group) {
    return $group->delete();
  }

  /**
   * @param int $groupId
   * @param int $userId
   * @return UsersMemberOfGroups | null
   */
  public function getUserMemberOfGroupByGroupIdAndUserId(int $groupId, int $userId) {
    return UsersMemberOfGroups::where('group_id', $groupId)
      ->where('user_id', $userId)
      ->first();
  }

  /**
   * @param int $groupId
   * @param int $userId
   * @param string|null $role
   * @param UsersMemberOfGroups|null $userMemberOfGroup
   * @return UsersMemberOfGroups|null
   */
  public function createOrUpdateUserMemberOfGroup(int $groupId, int $userId, ?string $role, $userMemberOfGroup = null) {
    if ($userMemberOfGroup == null) {
      $userMemberOfGroup = new UsersMemberOfGroups([
        'user_id' => $userId,
        'group_id' => $groupId,
        'role' => $role, ]);
    } else {
      $userMemberOfGroup->role = $role;
    }

    if (! $userMemberOfGroup->save()) {
      Logging::error('createUserMemberOfGroup', 'Could not create user member of group');

      return null;
    }

    return $userMemberOfGroup;
  }

  /**
   * @param UsersMemberOfGroups $userMemberOfGroup
   * @return boolean
   * @throws Exception
   */
  public function removeUserFromGroup($userMemberOfGroup) {
    return $userMemberOfGroup->delete();
  }

  /**
   * @param User $user
   * @return Group[]
   */
  public function getGroupsWhereUserIsNotIn($user) {
    $allGroups = $this->getAllGroups();
    $groupsToReturn = [];
    $userMemberOfGroups = $user->usersMemberOfGroups();
    foreach ($allGroups as $group) {
      $isInGroup = false;
      foreach ($userMemberOfGroups as $userMemberOfGroup) {
        if ($userMemberOfGroup->group()->id == $group->id) {
          $isInGroup = true;
          break;
        }
      }

      if (! $isInGroup) {
        $groupsToReturn[] = $group;
      }
    }

    return $groupsToReturn;
  }

  /**
   * @param Group $group
   * @return Group
   */
  public function getGroupStatisticsByGroup(Group $group) {
    $usersInGroups = $group->usersMemberOfGroups();

    $joinYears = [];

    $users_only_in_this_group = [];
    foreach ($usersInGroups as $user) {
      if (DB::table('users_member_of_groups')
        ->where('user_id', '=', $user->user_id)
        ->count() < 2) {
        $userD = $user->user();
        $userR = new stdClass();
        $userR->firstname = $userD->firstname;
        $userR->surname = $userD->surname;
        $userR->created_at = $user->created_at;

        $users_only_in_this_group[] = $userR;
      }

      $joinYear = date_format($user->created_at, 'Y');
      if (! in_array($joinYear, $joinYears)) {
        $joinYears[] = $joinYear;
      }
    }
    $group->users_only_in_this_group = $users_only_in_this_group;

    $users_grouped_by_join_year = [];
    foreach ($joinYears as $joinYear) {
      $year = new stdClass();
      $year->year = $joinYear;
      $userToAdd = [];
      foreach ($usersInGroups as $user) {
        $userJoinYear = date_format($user->created_at, 'Y');
        if (str_contains($joinYear, $userJoinYear)) {
          $userD = $user->user();
          $userR = new stdClass();
          $userR->firstname = $userD->firstname;
          $userR->surname = $userD->surname;
          $userR->created_at = $user->created_at;

          $userToAdd[] = $userR;
        }
      }
      $year->users = $userToAdd;
      $users_grouped_by_join_year[] = $year;
    }
    $group->users_grouped_by_join_year = $users_grouped_by_join_year;

    return $group;
  }
}
