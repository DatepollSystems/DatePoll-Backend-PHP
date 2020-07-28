<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\Group\Subgroup\ISubgroupRepository;
use App\Repositories\User\User\IUserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class SubgroupController extends Controller
{

  protected $subgroupRepository = null;
  protected $groupRepository = null;
  protected $userRepository = null;

  public function __construct(ISubgroupRepository $subgroupRepository, IGroupRepository $groupRepository,
                              IUserRepository $userRepository) {
    $this->subgroupRepository = $subgroupRepository;
    $this->groupRepository = $groupRepository;
    $this->userRepository = $userRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $subgroups = $this->subgroupRepository->getAllSubgroupsOrdered();

    return response()->json([
      'msg' => 'List of all subgroups',
      'subgroups' => $subgroups], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'description' => 'max:65535',
      'orderN' => 'integer',
      'group_id' => 'required|integer']);

    $groupId = $request->input('group_id');
    if ($this->groupRepository->getGroupById($groupId) == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $name = $request->input('name');
    $description = $request->input('description');
    $orderN = $request->input('orderN');

    $subgroup = $this->subgroupRepository->createOrUpdateSubgroup($name, $description, $groupId, $orderN);

    if ($subgroup == null) {
      return response()->json(['msg' => 'An error occurred'], 500);
    }

    return response()->json([
      'msg' => 'Subgroup created',
      'subgroup' => $subgroup], 201);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle($id) {
    $subgroup = $this->subgroupRepository->getSubgroupById($id);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $subgroup->users = $subgroup->getUsersWithRolesOrderedBySurname();

    return response()->json([
      'msg' => 'Subgroup information',
      'subgroup' => $subgroup]);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, $id) {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'description' => 'max:65535',
      'orderN' => 'integer',
      'group_id' => 'required|integer']);

    $subgroup = $this->subgroupRepository->getSubgroupById($id);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $groupId = $request->input('group_id');
    if ($this->groupRepository->getGroupById($groupId) == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $name = $request->input('name');
    $description = $request->input('description');
    $orderN = $request->input('orderN');

    $subgroup = $this->subgroupRepository->createOrUpdateSubgroup($name, $description, $groupId, $orderN, $subgroup);

    if ($subgroup == null) {
      return response()->json(['msg' => 'An error occurred'], 500);
    }

    return response()->json([
      'msg' => 'Subgroup updated',
      'subgroup' => $subgroup], 201);
  }

  /**
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete($id) {
    $subgroup = $this->subgroupRepository->getSubgroupById($id);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    if (!$this->subgroupRepository->deleteSubgroup($subgroup)) {
      return response()->json(['msg' => 'Subgroup deletion failed'], 500);
    }


    return response()->json(['msg' => 'Subgroup deleted!'], 200);
  }

  /**
   * Add user to subgroup
   *
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addUser(Request $request) {
    $this->validate($request, [
      'user_id' => 'required|integer',
      'subgroup_id' => 'required|integer',
      'role' => 'max:190']);

    $userID = $request->input('user_id');
    $subgroupID = $request->input('subgroup_id');
    $role = $request->input('role');

    if ($this->userRepository->getUserById($userID) == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $subgroup = $this->subgroupRepository->getSubgroupById($subgroupID);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    if ($this->subgroupRepository->getUserMemberOfSubgroupBySubgroupIdAndUserId($subgroupID, $userID) != null) {
      return response()->json(['msg' => 'User is already member of this subgroup'], 201);
    }

    $userMemberOfParentGroup = $this->groupRepository->getUserMemberOfGroupByGroupIdAndUserId($subgroup->group_id, $userID);
    if ($userMemberOfParentGroup == null) {
      if ($this->groupRepository->createOrUpdateUserMemberOfGroup($subgroup->group_id, $userID, null) == null) {
        return response()->json(['msg' => 'Could not add user to the parent group'], 500);
      }
    }

    $userMemberOfSubgroup = $this->subgroupRepository->createOrUpdateUserMemberOfSubgroup($subgroupID, $userID, $role);
    if ($userMemberOfSubgroup == null) {
      return response()->json(['msg' => 'Could not add user to this subgroup'], 500);
    }

    return response()->json([
      'msg' => 'Successfully added user to subgroup',
      'userMemberOfSubgroup' => $userMemberOfSubgroup], 201);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   * @throws Exception
   */
  public function removeUser(Request $request) {
    $this->validate($request, [
      'user_id' => 'required|integer',
      'subgroup_id' => 'required|integer']);

    $userID = $request->input('user_id');
    $subgroupID = $request->input('subgroup_id');

    if ($this->userRepository->getUserById($userID) == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $subgroup = $this->subgroupRepository->getSubgroupById($subgroupID);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $userMemberOfSubgroup = $this->subgroupRepository->getUserMemberOfSubgroupBySubgroupIdAndUserId($subgroupID, $userID);
    if ($userMemberOfSubgroup == null) {
      return response()->json(['msg' => 'User is not a member of this subgroup'], 201);
    }

    if (!$this->subgroupRepository->removeSubgroupForUser($userMemberOfSubgroup)) {
      return response()->json(['msg' => 'Could not remove user of this subgroup'], 500);
    }

    return response()->json(['msg' => 'Successfully removed user from subgroup'], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function updateUser(Request $request) {
    $this->validate($request, [
      'user_id' => 'required|integer',
      'subgroup_id' => 'required|integer',
      'role' => 'max:190']);

    $userID = $request->input('user_id');
    $subgroupID = $request->input('subgroup_id');
    $role = $request->input('role');

    if ($this->userRepository->getUserById($userID) == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $subgroup = $this->subgroupRepository->getSubgroupById($subgroupID);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $userMemberOfSubgroup = $this->subgroupRepository->getUserMemberOfSubgroupBySubgroupIdAndUserId($subgroupID, $userID);
    if ($userMemberOfSubgroup == null) {
      return response()->json(['msg' => 'User is not a member of this subgroup'], 404);
    }

    $userMemberOfSubgroup = $this->subgroupRepository->createOrUpdateUserMemberOfSubgroup($subgroupID, $userID, $role, $userMemberOfSubgroup);
    if ($userMemberOfSubgroup == null) {
      return response()->json(['msg' => 'Could not save UserMemberOfGroup'], 500);
    }

    return response()->json([
      'msg' => 'Successfully updated user in subgroup',
      'userMemberOfSubgroup' => $userMemberOfSubgroup], 200);
  }

  /**
   * @param int $userID
   * @return JsonResponse
   */
  public function joined($userID) {
    if ($this->userRepository->getUserById($userID) == null) {
      return response()->json([
        'msg' => 'User not found',
        'error_code' => 'user_not_found'], 404);
    }
    return response()->json([
      'msg' => 'List of joined subgroups',
      'subgroups' => $this->subgroupRepository->getJoinedSubgroupsReturnableByUserId($userID)], 200);
  }

  /**
   * @param int $userID
   * @return JsonResponse
   */
  public function free($userID) {
    if ($this->userRepository->getUserById($userID) == null) {
      return response()->json([
        'msg' => 'User not found',
        'error_code' => 'user_not_found'], 404);
    }

    return response()->json([
      'msg' => 'List of free subgroups',
      'subgroups' => $this->subgroupRepository->getFreeSubgroupsReturnableByUserId($userID)], 200);
  }
}
