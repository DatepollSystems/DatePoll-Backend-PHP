<?php

namespace App\Repositories\Group\Subgroup;

use App\Models\Subgroups\Subgroup;
use App\Models\Subgroups\UsersMemberOfSubgroups;
use Exception;

interface ISubgroupRepository {
  /**
   * @return Subgroup[]
   */
  public function getAllSubgroupsOrdered(): array;

  /**
   * @param int $id
   * @return Subgroup|null
   */
  public function getSubgroupById(int $id): ?Subgroup;

  /**
   * @param string $name
   * @param string $description
   * @param int $groupId
   * @param int|null $orderN
   * @param Subgroup|null $subgroup
   * @return Subgroup|null
   */
  public function createOrUpdateSubgroup(string $name, string $description, int $groupId, ?int $orderN = null, ?Subgroup $subgroup = null): ?Subgroup;

  /**
   * @param Subgroup $subgroup
   * @return boolean
   * @throws Exception
   */
  public function deleteSubgroup(Subgroup $subgroup): bool;

  /**
   * @param int $subgroupId
   * @param int $userId
   * @return UsersMemberOfSubgroups|null
   */
  public function getUserMemberOfSubgroupBySubgroupIdAndUserId(int $subgroupId, int $userId): ?UsersMemberOfSubgroups;

  /**
   * @param int $subgroupId
   * @param int $userId
   * @param string|null $role
   * @param UsersMemberOfSubgroups|null $userMemberOfSubgroup
   * @return UsersMemberOfSubgroups|null
   */
  public function createOrUpdateUserMemberOfSubgroup(int $subgroupId, int $userId, ?string $role = null, ?UsersMemberOfSubgroups $userMemberOfSubgroup = null): ?UsersMemberOfSubgroups;

  /**
   * @param int $userId
   * @param int $groupId
   * @return UsersMemberOfSubgroups[]
   */
  public function getUserMemberOfSubgroupsAndInGroups(int $userId, int $groupId): array;

  /**
   * @param UsersMemberOfSubgroups $userMemberOfSubgroup
   * @return boolean
   * @throws Exception
   */
  public function removeSubgroupForUser(UsersMemberOfSubgroups $userMemberOfSubgroup): bool;

  /**
   * @param int $userId
   * @return Subgroup[]
   */
  public function getSubgroupsWhereUserIsIn(int $userId): array;

  /**
   * @param int $userId
   * @return Subgroup[]
   */
  public function getSubgroupsWhereUserIsNotIn(int $userId): array;
}
