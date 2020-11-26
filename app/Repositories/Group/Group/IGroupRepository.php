<?php

namespace App\Repositories\Group\Group;

use App\Models\Groups\Group;
use App\Models\Groups\UsersMemberOfGroups;
use App\Models\User\User;
use Exception;
use Illuminate\Database\Eloquent\Collection;

interface IGroupRepository {
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
   * @param int $id
   * @return Group|null
   */
  public function getGroupById(int $id);

  /**
   * @param string $name
   * @param string $description
   * @param int|null $orderN
   * @param Group|null $group
   * @return Group | null
   */
  public function createOrUpdateGroup(string $name, string $description, $orderN = null, $group = null);

  /**
   * @param Group $group
   * @return boolean
   * @throws Exception
   */
  public function delete($group);

  /**
   * @param int $groupId
   * @param int $userId
   * @return UsersMemberOfGroups | null
   */
  public function getUserMemberOfGroupByGroupIdAndUserId(int $groupId, int $userId);

  /**
   * @param int $groupId
   * @param int $userId
   * @param string $role
   * @param UsersMemberOfGroups|null $userMemberOfGroup
   * @return UsersMemberOfGroups|null
   */
  public function createOrUpdateUserMemberOfGroup(int $groupId, int $userId, string $role, $userMemberOfGroup = null);

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
