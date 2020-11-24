<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\Group\Subgroup\ISubgroupRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserChange\IUserChangeRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller {
  protected IGroupRepository $groupRepository;
  protected ISubgroupRepository $subgroupRepository;
  protected IUserRepository $userRepository;
  protected IUserChangeRepository $userChangeRepository;

  public function __construct(
    IGroupRepository $groupRepository,
    IUserRepository $userRepository,
    ISubgroupRepository $subgroupRepository,
    IUserChangeRepository $userChangeRepository
  ) {
    $this->groupRepository = $groupRepository;
    $this->userRepository = $userRepository;
    $this->subgroupRepository = $subgroupRepository;
    $this->userChangeRepository = $userChangeRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $groups = $this->groupRepository->getAllGroupsWithSubgroupsOrdered();

    return response()->json([
      'msg' => 'List of all groups',
      'groups' => $groups, ], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'orderN' => 'integer',
      'description' => 'max:65535', ]);

    $name = $request->input('name');
    $orderN = $request->input('orderN');
    $description = $request->input('description');

    $group = $this->groupRepository->createOrUpdateGroup($name, $description, $orderN, null);

    if ($group == null) {
      return response()->json(['msg' => 'An error occurred'], 500);
    }

    return response()->json([
      'msg' => 'Group created',
      'group' => $group, ], 201);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(int $id) {
    $group = $this->groupRepository->getGroupById($id);
    if ($group == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $group = $this->groupRepository->getGroupStatisticsByGroup($group);
    $group->subgroups = $group->getSubgroupsOrdered();
    $group->users = $group->getUsersWithRolesOrderedBySurname();

    return response()->json([
      'msg' => 'Group information',
      'group' => $group, ]);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, int $id) {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'orderN' => 'integer',
      'description' => 'max:65535', ]);

    $group = $this->groupRepository->getGroupById($id);
    if ($group == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $name = $request->input('name');
    $description = $request->input('description');
    $orderN = $request->input('orderN');

    $group = $this->groupRepository->createOrUpdateGroup($name, $description, $orderN, $group);

    if ($group == null) {
      return response()->json(['msg' => 'An error occurred'], 500);
    }

    return response()->json([
      'msg' => 'Group updated',
      'group' => $group, ], 201);
  }

  /**
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(int $id) {
    $group = $this->groupRepository->getGroupById($id);
    if ($group == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    if ($this->groupRepository->delete($group)) {
      return response()->json(['msg' => 'Group deleted successfully'], 200);
    }

    return response()->json(['msg' => 'Group deletion failed'], 500);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addUser(Request $request) {
    $this->validate($request, [
      'user_id' => 'required|integer',
      'group_id' => 'required|integer',
      'role' => 'max:190', ]);

    $userID = $request->input('user_id');
    $groupID = $request->input('group_id');
    $role = $request->input('role');

    if ($this->userRepository->getUserById($userID) == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $group = $this->groupRepository->getGroupById($groupID);
    if ($group == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $userMemberOfGroup = $this->groupRepository->getUserMemberOfGroupByGroupIdAndUserId($groupID, $userID);
    if ($userMemberOfGroup != null) {
      return response()->json(['msg' => 'User is already member of this group'], 201);
    }

    $userMemberOfGroup = $this->groupRepository->createOrUpdateUserMemberOfGroup($groupID, $userID, $role);

    if ($userMemberOfGroup == null) {
      return response()->json(['msg' => 'Could not add user to this group'], 500);
    }

    $this->userChangeRepository->createUserChange('group', $userID, $request->auth->id, $group->name, null);

    return response()->json([
      'msg' => 'Successfully added user to group',
      'userMemberOfGroup' => $userMemberOfGroup, ], 201);
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
      'group_id' => 'required|integer', ]);

    $userID = $request->input('user_id');
    $groupID = $request->input('group_id');

    if ($this->userRepository->getUserById($userID) == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $group = $this->groupRepository->getGroupById($groupID);
    if ($group == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $userMemberOfGroup = $this->groupRepository->getUserMemberOfGroupByGroupIdAndUserId($groupID, $userID);
    if ($userMemberOfGroup == null) {
      return response()->json(['msg' => 'User is not a member of this group'], 201);
    }

    if (! $this->groupRepository->removeUserFromGroup($userMemberOfGroup)) {
      return response()->json(['msg' => 'Could not remove user of this group'], 500);
    }

    /* Remove user from child subgroups */
    $userMemberOfSubgroupsToRemove = $this->subgroupRepository->getUserMemberOfSubgroupsAndInGroups($userID, $groupID);

    foreach ($userMemberOfSubgroupsToRemove as $userMemberOfSubgroupToRemove) {
      if (! $this->subgroupRepository->removeSubgroupForUser($userMemberOfSubgroupToRemove)) {
        return response()->json(['msg' => 'Could not remove user of child subgroups'], 500);
      }
    }

    $this->userChangeRepository->createUserChange('group', $userID, $request->auth->id, null, $group->name);

    return response()->json([
      'msg' => 'Successfully removed user from group',
      'userWasMemberOfSubgroups' => $userMemberOfSubgroupsToRemove, ], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function updateUser(Request $request) {
    $this->validate($request, [
      'user_id' => 'required|integer',
      'group_id' => 'required|integer',
      'role' => 'max:190', ]);

    $userID = $request->input('user_id');
    $groupID = $request->input('group_id');
    $role = $request->input('role');

    if ($this->userRepository->getUserById($userID)) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    if ($this->groupRepository->getGroupById($groupID) == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $userMemberOfGroup = $this->groupRepository->getUserMemberOfGroupByGroupIdAndUserId($groupID, $userID);
    if ($userMemberOfGroup == null) {
      return response()->json(['msg' => 'User is not a member of this group'], 404);
    }

    $userMemberOfGroup = $this->groupRepository->createOrUpdateUserMemberOfGroup(
      $groupID,
      $userID,
      $role,
      $userMemberOfGroup
    );
    if ($userMemberOfGroup == null) {
      return response()->json(['msg' => 'Could not save UserMemberOfGroup'], 500);
    }

    return response()->json([
      'msg' => 'Successfully updated user in group',
      'userMemberOfGroup' => $userMemberOfGroup, ], 200);
  }

  /**
   * @param int $userID
   * @return JsonResponse
   */
  public function joined(int $userID) {
    $user = $this->userRepository->getUserById($userID);
    if ($user == null) {
      return response()->json([
        'msg' => 'User not found',
        'error_code' => 'user_not_found', ], 404);
    }

    $groupsToReturn = [];
    $userMemberOfGroups = $user->usersMemberOfGroups();
    foreach ($userMemberOfGroups as $userMemberOfGroup) {
      $group = $userMemberOfGroup->group();
      $groupsToReturn[] = $group;
    }

    return response()->json([
      'msg' => 'List of joined groups',
      'groups' => $groupsToReturn, ], 200);
  }

  /**
   * @param int $userID
   * @return JsonResponse
   */
  public function free(int $userID) {
    $user = $this->userRepository->getUserById($userID);
    if ($user == null) {
      return response()->json([
        'msg' => 'User not found',
        'error_code' => 'user_not_found', ], 404);
    }

    $groupsToReturn = $this->groupRepository->getGroupsWhereUserIsNotIn($user);

    return response()->json([
      'msg' => 'List of free groups',
      'groups' => $groupsToReturn, ], 200);
  }
}
