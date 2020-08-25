<?php

namespace App\Repositories\Group\Group;

use App\Models\Groups\Group;
use App\Models\Groups\UsersMemberOfGroups;
use App\Models\User\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IGroupRepository
{
  /**
   * @return Group[]|Collection
   */
  public function getAllGroups();

  /**
   * @return Group[]|Collection
   */
  public function getAllGroupsOrdered();

  /**
   * @return Group[]|Collection
   */
  public function getAllGroupsWithSubgroupsOrdered();

  /**
   * @param $id
   * @return Group|null
   */
  public function getGroupById($id);

  /**
   * @param string $name
   * @param string $description
   * @param int|null $orderN
   * @param Group|null $group
   * @return Group
   */
  public function createOrUpdateGroup($name, $description, $orderN = null, $group = null);

  /**
   * @param Group $group
   * @return boolean
   * @throws Exception
   */
  public function delete($group);

  /**
   * @param $groupId
   * @param $userId
   * @return UsersMemberOfGroups | null
   */
  public function getUserMemberOfGroupByGroupIdAndUserId($groupId, $userId);

  /**
   * @param int $groupId
   * @param int $userId
   * @param string $role
   * @param UsersMemberOfGroups|null $userMemberOfGroup
   * @return UsersMemberOfGroups|null
   */
  public function createOrUpdateUserMemberOfGroup($groupId, $userId, $role, $userMemberOfGroup = null);

  /**
   * @param UsersMemberOfGroups $userMemberOfGroup
   * @return boolean
   * @throws Exception
   */
  public function removeUserFromGroup($userMemberOfGroup);

  /**
   * @param User $user
   * @return Group[]
   */
  public function getGroupsWhereUserIsNotIn($user);

  /**
   * @param Group $group
   * @return Group
   */
  public function getGroupStatisticsByGroup(Group $group);
}
