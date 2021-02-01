<?php

namespace App\Repositories\Group\Subgroup;

use App\Logging;
use App\Models\Subgroups\Subgroup;
use App\Models\Subgroups\UsersMemberOfSubgroups;
use Exception;

class SubgroupRepository implements ISubgroupRepository {
  /**
   * @return Subgroup[]
   */
  public function getAllSubgroupsOrdered(): array {
    return Subgroup::orderBy('orderN')
      ->get()->all();
  }

  /**
   * @param int $id
   * @return Subgroup|null
   */
  public function getSubgroupById(int $id): ?Subgroup {
    return Subgroup::find($id);
  }

  /**
   * @param string $name
   * @param string $description
   * @param int $groupId
   * @param int|null $orderN
   * @param Subgroup|null $subgroup
   * @return Subgroup|null
   */
  public function createOrUpdateSubgroup(string $name, string $description, int $groupId, ?int $orderN = null, ?Subgroup $subgroup = null): ?Subgroup {
    if ($subgroup == null) {
      $subgroup = new Subgroup([
        'name' => $name,
        'description' => $description,
        'orderN' => $orderN,
        'group_id' => $groupId, ]);
    } else {
      $subgroup->name = $name;
      $subgroup->description = $description;
      $subgroup->group_id = $groupId;
      if ($orderN == null) {
        $subgroup->orderN = 0;
      } else {
        $subgroup->orderN = $orderN;
      }
    }

    if (! $subgroup->save()) {
      Logging::error('createOrUpdateSubgroup', 'Could not save subgroup!');

      return null;
    }

    return $subgroup;
  }

  /**
   * @param Subgroup $subgroup
   * @return boolean
   * @throws Exception
   */
  public function deleteSubgroup(Subgroup $subgroup): bool {
    return $subgroup->delete();
  }

  /**
   * @param int $subgroupId
   * @param int $userId
   * @return UsersMemberOfSubgroups|null
   */
  public function getUserMemberOfSubgroupBySubgroupIdAndUserId(int $subgroupId, int $userId): ?UsersMemberOfSubgroups {
    return UsersMemberOfSubgroups::where('user_id', $userId)
      ->where('subgroup_id', $subgroupId)
      ->first();
  }

  /**
   * @param int $subgroupId
   * @param int $userId
   * @param string|null $role
   * @param UsersMemberOfSubgroups|null $userMemberOfSubgroup
   * @return UsersMemberOfSubgroups|null
   */
  public function createOrUpdateUserMemberOfSubgroup(int $subgroupId, int $userId, ?string $role = null, ?UsersMemberOfSubgroups $userMemberOfSubgroup = null): ?UsersMemberOfSubgroups {
    if ($userMemberOfSubgroup == null) {
      $userMemberOfSubgroup = new UsersMemberOfSubgroups([
        'user_id' => $userId,
        'subgroup_id' => $subgroupId,
        'role' => $role, ]);
    } else {
      $userMemberOfSubgroup->role = $role;
    }

    if (! $userMemberOfSubgroup->save()) {
      Logging::error('createOrUpdateUserMemberOfSubgroup', 'Could not save userMemberOfSubgroup');

      return null;
    }

    return $userMemberOfSubgroup;
  }

  /**
   * @param int $userId
   * @param int $groupId
   * @return UsersMemberOfSubgroups[]
   */
  public function getUserMemberOfSubgroupsAndInGroups(int $userId, int $groupId): array {
    $userMemberOfSubgroups = [];
    foreach (UsersMemberOfSubgroups::where('user_id', $userId)
      ->get()->all() as $userMemberOfSubgroup) {
      if ($userMemberOfSubgroup->subgroup()->group_id = $groupId) {
        $userMemberOfSubgroups[] = $userMemberOfSubgroup;
      }
    }

    return $userMemberOfSubgroups;
  }

  /**
   * @param UsersMemberOfSubgroups $userMemberOfSubgroup
   * @return boolean
   * @throws Exception
   */
  public function removeSubgroupForUser(UsersMemberOfSubgroups $userMemberOfSubgroup): bool {
    return $userMemberOfSubgroup->delete();
  }

  /**
   * @param int $userId
   * @return Subgroup[]
   */
  public function getJoinedSubgroupsReturnableByUserId(int $userId): array {
    $subgroupsToReturn = [];
    foreach (UsersMemberOfSubgroups::where('user_id', $userId)
      ->get()->all() as $userMemberOfSubgroup) {
      $subgroup = $userMemberOfSubgroup->subgroup();

      $subgroup['group_name'] = $subgroup->group()->name;

      $subgroupsToReturn[] = $subgroup;
    }

    return $subgroupsToReturn;
  }

  /**
   * @param int $userId
   * @return Subgroup[]
   */
  public function getFreeSubgroupsReturnableByUserId(int $userId): array {
    $subgroupsToReturn = [];
    foreach ($this->getAllSubgroupsOrdered() as $subgroup) {
      $isInSubgroup = false;
      foreach (UsersMemberOfSubgroups::where('user_id', $userId)
        ->get()->all() as $userMemberOfSubgroup) {
        if ($userMemberOfSubgroup->subgroup_id == $subgroup->id) {
          $isInSubgroup = true;
          break;
        }
      }

      if (! $isInSubgroup) {
        $subgroup['group_name'] = $subgroup->group->name;
        $subgroupsToReturn[] = $subgroup;
      }
    }

    return $subgroupsToReturn;
  }
}
