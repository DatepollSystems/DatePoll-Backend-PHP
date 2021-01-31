<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Permissions;
use App\Repositories\Group\Group\IGroupRepository;
use App\Repositories\Group\Subgroup\ISubgroupRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserChange\IUserChangeRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class GroupController extends Controller {
  public function __construct(
    protected IGroupRepository $groupRepository,
    protected IUserRepository $userRepository,
    protected ISubgroupRepository $subgroupRepository,
    protected IUserChangeRepository $userChangeRepository
  ) {
  }

  /**
   * @return JsonResponse
   */
  public function getAll(): JsonResponse {
    $groups = $this->groupRepository->getAllGroupsWithSubgroupsOrdered();

    return response()->json([
      'msg' => 'List of all groups',
      'groups' => $groups, ], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'orderN' => 'integer|nullable',
      'description' => 'max:65535',
      'permissions' => 'array',
      'permissions.*' => 'required|max:190', ]);

    $name = $request->input('name');
    $orderN = $request->input('orderN');
    $description = $request->input('description');

    $group = $this->groupRepository->createOrUpdateGroup($name, $description, $orderN, null);

    if ($group == null) {
      return response()->json(['msg' => 'An error occurred'], 500);
    }

    $permissions = $request->input('permissions');

    if ($request->auth->hasPermission(Permissions::$MANAGEMENT_EXTRA_USER_PERMISSIONS)) {
      /** @noinspection NestedPositiveIfStatementsInspection */
      if (! $this->groupRepository->createOrUpdatePermissionsForGroup($permissions, $group)) {
        return response()->json(['msg' => 'Failed during permission clearing...'], 500);
      }
    }

    return response()->json([
      'msg' => 'Group created',
      'group' => $group, ], 201);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(int $id): JsonResponse {
    $group = $this->groupRepository->getGroupById($id);
    if ($group == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $group = $this->groupRepository->getGroupStatisticsByGroup($group);

    return response()->json([
      'msg' => 'Group information',
      'group' => $group, ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(AuthenticatedRequest $request, int $id): JsonResponse {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'orderN' => 'integer|nullable',
      'description' => 'max:65535',
      'permissions' => 'array',
      'permissions.*' => 'required|max:190',]);

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

    $permissions = $request->input('permissions');

    if ($request->auth->hasPermission(Permissions::$MANAGEMENT_EXTRA_USER_PERMISSIONS)) {
      /** @noinspection NestedPositiveIfStatementsInspection */
      if (! $this->groupRepository->createOrUpdatePermissionsForGroup($permissions, $group)) {
        return response()->json(['msg' => 'Failed during permission clearing...'], 500);
      }
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
  public function delete(int $id): JsonResponse {
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
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addUser(AuthenticatedRequest $request): JsonResponse {
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
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   * @throws Exception
   */
  public function removeUser(AuthenticatedRequest $request): JsonResponse {
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
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function updateUser(AuthenticatedRequest $request): JsonResponse {
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

    $this->userChangeRepository->createUserChange('group', $userID, $request->auth->id, $userMemberOfGroup->role, $userMemberOfGroup->role);

    return response()->json([
      'msg' => 'Successfully updated user in group',
      'userMemberOfGroup' => $userMemberOfGroup, ], 200);
  }

  /**
   * @param int $userID
   * @return JsonResponse
   */
  public function joined(int $userID): JsonResponse {
    $user = $this->userRepository->getUserById($userID);
    if ($user == null) {
      return response()->json([
        'msg' => 'User not found',
        'error_code' => 'user_not_found', ], 404);
    }

    return response()->json([
      'msg' => 'List of joined groups',
      'groups' => $user->getGroups(), ], 200);
  }

  /**
   * @param int $userID
   * @return JsonResponse
   */
  public function free(int $userID): JsonResponse {
    $user = $this->userRepository->getUserById($userID);
    if ($user == null) {
      return response()->json([
        'msg' => 'User not found',
        'error_code' => 'user_not_found', ], 404);
    }

    return response()->json([
      'msg' => 'List of free groups',
      'groups' => $this->groupRepository->getGroupsWhereUserIsNotIn($user->id), ], 200);
  }
}
