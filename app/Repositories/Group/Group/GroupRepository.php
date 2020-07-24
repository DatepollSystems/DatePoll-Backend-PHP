<?php


namespace App\Repositories\Group\Group;


use App\Logging;
use App\Models\Groups\Group;
use App\Models\Groups\UsersMemberOfGroups;
use App\Models\User\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

class GroupRepository implements IGroupRepository
{
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
   * @param $id
   * @return Group|null
   */
  public function getGroupById($id) {
    return Group::find($id);
  }

  /**
   * @param string $name
   * @param string $description
   * @param int $orderN
   * @param Group $group
   * @return Group
   */
  public function createOrUpdateGroup($name, $description, $orderN = null, $group = null) {
    if ($group == null) {
      $group = new Group([
        'name' => $name,
        'orderN' => $orderN,
        'description' => $description]);
    } else {
      $group->name = $name;
      $group->description = $description;
      $group->orderN = $orderN;
      if ($orderN == null) {
        $group->orderN = 0;
      }
    }

    if (!$group->save()) {
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
   * @param $groupId
   * @param $userId
   * @return UsersMemberOfGroups | null
   */
  public function getUserMemberOfGroupByGroupIdAndUserId($groupId, $userId) {
    return UsersMemberOfGroups::where('group_id', $groupId)
                              ->where('user_id', $userId)
                              ->first();
  }

  /**
   * @param int $groupId
   * @param int $userId
   * @param string $role
   * @param UsersMemberOfGroups $userMemberOfGroup
   * @return UsersMemberOfGroups|null
   */
  public function createOrUpdateUserMemberOfGroup($groupId, $userId, $role, $userMemberOfGroup = null) {
    if ($userMemberOfGroup == null) {
      $userMemberOfGroup = new UsersMemberOfGroups([
        'user_id' => $userId,
        'group_id' => $groupId,
        'role' => $role]);
    } else {
      $userMemberOfGroup->role = $role;
    }

    if (!$userMemberOfGroup->save()) {
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
    $groupsToReturn = array();
    $userMemberOfGroups = $user->usersMemberOfGroups();
    foreach ($allGroups as $group) {
      $isInGroup = false;
      foreach ($userMemberOfGroups as $userMemberOfGroup) {
        if ($userMemberOfGroup->group()->id == $group->id) {
          $isInGroup = true;
          break;
        }
      }

      if (!$isInGroup) {
        $groupsToReturn[] = $group;
      }
    }
    return $groupsToReturn;
  }
}