<?php

namespace App\Repositories\Group\Group;

use App\Models\Groups\Group;
use App\Models\Groups\UsersMemberOfGroups;
use Exception;

interface IGroupRepository {
  /**
   * @return Group[]
   */
  public function getAllGroups(): array;

  /**
   * @return Group[]
   */
  public function getAllGroupsOrdered(): array;

  /**
   * @return Group[]
   */
  public function getAllGroupsWithSubgroupsOrdered(): array;

  /**
   * @param int $id
   * @return Group|null
   */
  public function getGroupById(int $id): ?Group;

  /**
   * @param string $name
   * @param string $description
   * @param int|null $orderN
   * @param Group|null $group
   * @return Group|null
   */
  public function createOrUpdateGroup(string $name, string $description, ?int $orderN = null, ?Group $group = null): ?Group;

  /**
   * @param Group $group
   * @return boolean
   * @throws Exception
   */
  public function delete(Group $group): bool;

  /**
   * @param int $groupId
   * @param int $userId
   * @return UsersMemberOfGroups|null
   */
  public function getUserMemberOfGroupByGroupIdAndUserId(int $groupId, int $userId): ?UsersMemberOfGroups;

  /**
   * @param int $groupId
   * @param int $userId
   * @param string|null $role
   * @param UsersMemberOfGroups|null $userMemberOfGroup
   * @return UsersMemberOfGroups|null
   */
  public function createOrUpdateUserMemberOfGroup(int $groupId, int $userId, ?string $role, ?UsersMemberOfGroups $userMemberOfGroup = null): ?UsersMemberOfGroups;

  /**
   * @param UsersMemberOfGroups $userMemberOfGroup
   * @return boolean
   * @throws Exception
   */
  public function removeUserFromGroup(UsersMemberOfGroups $userMemberOfGroup): bool;

  /**
   * @param int $userId
   * @return Group[]
   */
  public function getGroupsWhereUserIsNotIn(int $userId): array;

  /**
   * @param Group $group
   * @return Group
   */
  public function getGroupStatisticsByGroup(Group $group): Group;

  /**
   * @param string[]|array $permissions
   * @param Group $group
   * @return bool
   */
  public function createOrUpdatePermissionsForGroup(array $permissions, Group $group): bool;
}
