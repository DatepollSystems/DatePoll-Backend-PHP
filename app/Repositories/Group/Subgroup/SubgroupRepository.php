<?php


namespace App\Repositories\Group\Subgroup;

use App\Logging;
use App\Models\Subgroups\Subgroup;
use App\Models\Subgroups\UsersMemberOfSubgroups;
use Exception;
use Illuminate\Database\Eloquent\Collection;
use stdClass;

class SubgroupRepository implements ISubgroupRepository
{
  /**
   * @return Subgroup[] | Collection<Subgroup>
   */
  public function getAllSubgroupsOrdered() {
    return Subgroup::orderBy('orderN')
                   ->get();
  }

  /**
   * @param int $id
   * @return Subgroup|null
   */
  public function getSubgroupById($id) {
    return Subgroup::find($id);
  }

  /**
   * @param string $name
   * @param string $description
   * @param int $groupId
   * @param int $orderN
   * @param Subgroup|null $subgroup
   * @return Subgroup|null
   */
  public function createOrUpdateSubgroup($name, $description, $groupId, $orderN, $subgroup = null) {
    if ($subgroup == null) {
      $subgroup = new Subgroup([
        'name' => $name,
        'description' => $description,
        'orderN' => $orderN,
        'group_id' => $groupId]);
    } else {
      $subgroup->name = $name;
      $subgroup->description = $description;
      $subgroup->group_id = $groupId;
      $subgroup->orderN = $orderN;
      if ($orderN == null) {
        $subgroup->orderN = 0;
      }
    }

    if (!$subgroup->save()) {
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
  public function deleteSubgroup($subgroup) {
    return $subgroup->delete();
  }

  /**
   * @param int $subgroupId
   * @param int $userId
   * @return UsersMemberOfSubgroups|null
   */
  public function getUserMemberOfSubgroupBySubgroupIdAndUserId($subgroupId, $userId) {
    return UsersMemberOfSubgroups::where('user_id', $userId)
                                 ->where('subgroup_id', $subgroupId)
                                 ->first();
  }

  /**
   * @param int $subgroupId
   * @param int $userId
   * @param string $role
   * @param UsersMemberOfSubgroups|null $userMemberOfSubgroup
   * @return UsersMemberOfSubgroups|null
   */
  public function createOrUpdateUserMemberOfSubgroup($subgroupId, $userId, $role, $userMemberOfSubgroup = null) {
    if ($userMemberOfSubgroup == null) {
      $userMemberOfSubgroup = new UsersMemberOfSubgroups([
        'user_id' => $userId,
        'subgroup_id' => $subgroupId,
        'role' => $role]);
    } else {
      $userMemberOfSubgroup->role = $role;
    }

    if (!$userMemberOfSubgroup->save()) {
      Logging::error('createOrUpdateUserMemberOfSubgroup', 'Could not save userMemberOfSubgroup');
      return null;
    }

    return $userMemberOfSubgroup;
  }

  /**
   * @param int $userId
   * @param int $groupId
   * @return array|UsersMemberOfSubgroups[]
   */
  public function getUserMemberOfSubgroupsAndInGroups($userId, $groupId) {
    $userMemberOfSubgroups = array();
    $userMemberOfSubgroupsS = UsersMemberOfSubgroups::where('user_id', $userId)
                                                    ->get();
    foreach ($userMemberOfSubgroupsS as $userMemberOfSubgroupI) {
      if ($userMemberOfSubgroupI->subgroup()->group_id = $groupId) {
        $userMemberOfSubgroups[] = $userMemberOfSubgroupI;
      }
    }
    return $userMemberOfSubgroups;
  }

  /**
   * @param UsersMemberOfSubgroups $userMemberOfSubgroup
   * @return boolean
   * @throws Exception
   */
  public function removeSubgroupForUser($userMemberOfSubgroup) {
    return $userMemberOfSubgroup->delete();
  }

  /**
   * @param int $userId
   * @return stdClass[]|Subgroup[]
   */
  public function getJoinedSubgroupsReturnableByUserId($userId) {
    $subgroupsToReturn = array();
    $userMemberOfSubgroups = UsersMemberOfSubgroups::where('user_id', $userId)
                                                   ->get();

    foreach ($userMemberOfSubgroups as $userMemberOfSubgroup) {
      $subgroup = $userMemberOfSubgroup->subgroup();

      $subgroup['group_name'] = $subgroup->group()->name;

      $subgroupsToReturn[] = $subgroup;
    }

    return $subgroupsToReturn;
  }

  /**
   * @param int $userId
   * @return stdClass[]|Subgroup[]
   */
  public function getFreeSubgroupsReturnableByUserId($userId) {
    $allSubgroups = $this->getAllSubgroupsOrdered();
    $subgroupsToReturn = array();
    $userMemberOfSubgroups = UsersMemberOfSubgroups::where('user_id', $userId)
                                                   ->get();
    foreach ($allSubgroups as $subgroup) {
      $isInSubgroup = false;
      foreach ($userMemberOfSubgroups as $userMemberOfSubgroup) {
        if ($userMemberOfSubgroup->subgroup_id == $subgroup->id) {
          $isInSubgroup = true;
          break;
        }
      }

      if (!$isInSubgroup) {
        $subgroup['group_name'] = $subgroup->group()->name;
        $subgroupsToReturn[] = $subgroup;
      }
    }

    return $subgroupsToReturn;
  }
}
