<?php

namespace App\Repositories\Group\Subgroup;

use App\Models\Subgroups\Subgroup;
use App\Models\Subgroups\UsersMemberOfSubgroups;
use Exception;
use stdClass;

interface ISubgroupRepository
{
  /**
   * @return Subgroup[]
   */
  public function getAllSubgroupsOrdered();

  /**
   * @param int $id
   * @return Subgroup|null
   */
  public function getSubgroupById($id);

  /**
   * @param string $name
   * @param string $description
   * @param int $groupId
   * @param int $orderN
   * @param Subgroup $subgroup
   * @return Subgroup|null
   */
  public function createOrUpdateSubgroup($name, $description, $groupId, $orderN, $subgroup = null);

  /**
   * @param Subgroup $subgroup
   * @return boolean
   * @throws Exception
   */
  public function deleteSubgroup($subgroup);

  /**
   * @param int $subgroupId
   * @param int $userId
   * @return UsersMemberOfSubgroups|null
   */
  public function getUserMemberOfSubgroupBySubgroupIdAndUserId($subgroupId, $userId);

  /**
   * @param int $subgroupId
   * @param int $userId
   * @param string $role
   * @param UsersMemberOfSubgroups $userMemberOfSubgroup
   * @return UsersMemberOfSubgroups
   */
  public function createOrUpdateUserMemberOfSubgroup($subgroupId, $userId, $role, $userMemberOfSubgroup = null);

  /**
   * @param int $userId
   * @param int $groupId
   * @return array|UsersMemberOfSubgroups[]
   */
  public function getUserMemberOfSubgroupsAndInGroups($userId, $groupId);

  /**
   * @param UsersMemberOfSubgroups $userMemberOfSubgroup
   * @return boolean
   * @throws Exception
   */
  public function removeSubgroupForUser($userMemberOfSubgroup);

  /**
   * @param int $userId
   * @return stdClass[]
   */
  public function getJoinedSubgroupsReturnableByUserId($userId);

  /**
   * @param int $userId
   * @return stdClass[]
   */
  public function getFreeSubgroupsReturnableByUserId($userId);
}