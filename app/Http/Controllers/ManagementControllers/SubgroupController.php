<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\Groups\Group;
use App\Models\Groups\UsersMemberOfGroups;
use App\Models\Subgroups\Subgroup;
use App\Models\Subgroups\UsersMemberOfSubgroups;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class SubgroupController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function getAll() {
    $subgroups = Subgroup::all();

    return response(['msg' => 'List of all subgroups', 'subgroups' => $subgroups], 200);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @return Response
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, ['name' => 'required|max:190|min:1', 'description' => 'max:65535', 'group_id' => 'required|integer']);

    $group_id = $request->input('group_id');

    if (Group::find($group_id) == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $name = $request->input('name');
    $description = $request->input('description');

    $subgroup = new Subgroup(['name' => $name, 'description' => $description, 'group_id' => $group_id]);

    if ($subgroup->save()) {
      $subgroup->view_subgroup = ['href' => 'api/v1/management/subgroups/' . $subgroup->id, 'method' => 'GET'];

      $response = ['msg' => 'Subgroup created', 'subgroup' => $subgroup];

      return response()->json($response, 201);
    }

    $response = ['msg' => 'An error occurred'];

    return response()->json($response, 500);
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return Response
   */
  public function getSingle($id) {
    $subgroup = Subgroup::find($id);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $usersToShow = [];

    $usersMembersOfSubgroups = $subgroup->usersMemberOfSubgroups();
    foreach ($usersMembersOfSubgroups as $userMemberOfSubgroup) {
      $user = new \stdClass();
      $user->id = $userMemberOfSubgroup->user()->id;
      $user->firstname = $userMemberOfSubgroup->user()->firstname;
      $user->surname = $userMemberOfSubgroup->user()->surname;
      $user->role = $userMemberOfSubgroup->role;

      $usersToShow[] = $user;
    }

    $subgroup->users = $usersToShow;

    $subgroup->view_subgroups = ['href' => 'api/v1/management/subgroups', 'method' => 'GET'];

    $response = ['msg' => 'Subgroup information', 'subgroup' => $subgroup];
    return response()->json($response);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param int $id
   * @return Response
   * @throws ValidationException
   */
  public function update(Request $request, $id) {
    $this->validate($request, ['name' => 'required|max:255|min:1', 'description' => 'max:65535', 'group_id' => 'required|integer']);

    $subgroup = Subgroup::find($id);

    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $group_id = $request->input('group_id');

    if (Group::find($group_id) == null) {
      return response()->json(['msg' => 'Group not found', 'error_code' => 'group_id_incorrect'], 404);
    }

    $name = $request->input('name');
    $description = $request->input('description');

    $subgroup->name = $name;
    $subgroup->description = $description;
    $subgroup->group_id = $group_id;

    if ($subgroup->save()) {
      $subgroup->view_subgroup = ['href' => 'api/v1/management/subgroup/' . $subgroup->id, 'method' => 'GET'];

      $response = ['msg' => 'Subgroup updated', 'subgroup' => $subgroup];

      return response()->json($response, 201);
    }

    $response = ['msg' => 'An error occurred'];

    return response()->json($response, 500);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return Response
   */
  public function delete($id) {
    $subgroup = Subgroup::find($id);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    if (!$subgroup->delete()) {
      return response()->json(['msg' => 'Subgroup deletion failed'], 500);
    }

    $response = ['msg' => 'Subgroup deleted', 'create' => ['href' => 'api/v1/management/subgroup', 'method' => 'POST', 'params' => 'name, description, group_id']];

    return response()->json($response, 200);
  }

  /**
   * Add user to subgroup
   *
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addUser(Request $request) {
    $this->validate($request, ['user_id' => 'required|integer', 'subgroup_id' => 'required|integer', 'role' => 'max:190']);

    $userID = $request->input('user_id');
    $subgroupID = $request->input('subgroup_id');
    $role = $request->input('role');

    if (!User::exists($userID)) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $subgroup = Subgroup::find($subgroupID);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $userMemberOfSubgroup = UsersMemberOfSubgroups::where('user_id', $userID)->where('subgroup_id', $subgroupID)->first();
    if ($userMemberOfSubgroup != null) {
      return response()->json(['msg' => 'User is already member of this subgroup'], 201);
    }

    $userMemberOfParentGroup = UsersMemberOfGroups::where('user_id', $userID)->where('group_id', $subgroup->group_id)->first();
    if ($userMemberOfParentGroup == null) {
      $userMemberOfParentGroup = new UsersMemberOfGroups(['group_id' => $subgroup->group_id, 'user_id' => $userID]);

      if (!$userMemberOfParentGroup->save()) {
        return response()->json(['msg' => 'Could not add user to the parent group'], 500);
      }
    }

    $userMemberOfSubgroup = new UsersMemberOfSubgroups(['subgroup_id' => $subgroupID, 'user_id' => $userID, 'role' => $role]);

    if (!$userMemberOfSubgroup->save()) {
      return response()->json(['msg' => 'Could not add user to this subgroup'], 500);
    }

    $response = ['msg' => 'Successfully added user to subgroup', 'userMemberOfSubgroup' => $userMemberOfSubgroup, 'removeUser' => ['href' => 'api/v1/management/subgroup/removeUser', 'method' => 'POST', 'params' => 'subgroup_id, user_id']];

    return response()->json($response, 201);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function removeUser(Request $request) {
    $this->validate($request, ['user_id' => 'required|integer', 'subgroup_id' => 'required|integer']);

    $userID = $request->input('user_id');
    $subgroupID = $request->input('subgroup_id');

    $user = User::find($userID);
    if ($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $subgroup = Subgroup::find($subgroupID);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $userMemberOfSubgroup = UsersMemberOfSubgroups::where('subgroup_id', $subgroupID)->where('user_id', $userID)->first();
    if ($userMemberOfSubgroup == null) {
      return response()->json(['msg' => 'User is not a member of this subgroup'], 201);
    }

    if (!$userMemberOfSubgroup->delete()) {
      return response()->json(['msg' => 'Could not remove user of this subgroup'], 500);
    }

    $response = ['msg' => 'Successfully removed user from subgroup', 'addUser' => ['href' => 'api/v1/management/subgroup/addUser', 'method' => 'POST', 'params' => 'subgroup_id, user_id, role']];

    return response()->json($response, 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function updateUser(Request $request) {
    $this->validate($request, ['user_id' => 'required|integer', 'subgroup_id' => 'required|integer', 'role' => 'max:190']);

    $userID = $request->input('user_id');
    $subgroupID = $request->input('subgroup_id');
    $role = $request->input('role');

    if (!User::exists($userID)) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    if (Subgroup::find($subgroupID) == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $userMemberOfSubgroup = UsersMemberOfSubgroups::where('subgroup_id', $subgroupID)->where('user_id', $userID)->first();
    if ($userMemberOfSubgroup == null) {
      return response()->json(['msg' => 'User is not a member of this subgroup'], 404);
    }

    $userMemberOfSubgroup->role = $role;
    if (!$userMemberOfSubgroup->save()) {
      return response()->json(['msg' => 'Could not save UserMemberOfGroup'], 500);
    }

    $response = ['msg' => 'Successfully updated user in subgroup', 'userMemberOfSubgroup' => $userMemberOfSubgroup, 'addUser' => ['href' => 'api/v1/management/subgroup/addUser', 'method' => 'POST', 'params' => 'group_id, user_id, role']];

    return response()->json($response, 200);
  }

  /**
   * @param $userID
   * @return JsonResponse
   */
  public function joined($userID) {
    $user = User::find($userID);
    if ($user == null) {
      return response()->json(['msg' => 'User not found', 'error_code' => 'user_not_found'], 404);
    }

    $subgroupsToReturn = array();

    $userMemberOfSubgroups = UsersMemberOfSubgroups::where('user_id', $userID)->get();
    foreach ($userMemberOfSubgroups as $userMemberOfSubgroup) {
      $subgroup = $userMemberOfSubgroup->subgroup();

      $subgroup->group_name = $subgroup->group()->name;

      $subgroup->view_subgroup = ['href' => 'api/v1/management/subgroups/' . $subgroup->id, 'method' => 'GET'];

      $subgroupsToReturn[] = $subgroup;
    }

    return response()->json(['msg' => 'List of joined subgroups', 'subgroups' => $subgroupsToReturn], 200);
  }

  /**
   * @param $userID
   * @return JsonResponse
   */
  public function free($userID) {
    $user = User::find($userID);
    if ($user == null) {
      return response()->json(['msg' => 'User not found', 'error_code' => 'user_not_found'], 404);
    }

    $allSubgroups = Subgroup::all();
    $subgroupsToReturn = array();
    $userMemberOfSubgroups = UsersMemberOfSubgroups::where('user_id', $userID)->get();
    foreach ($allSubgroups as $subgroup) {
      $isInSubgroup = false;
      foreach ($userMemberOfSubgroups as $userMemberOfSubgroup) {
        if ($userMemberOfSubgroup->subgroup()->id == $subgroup->id) {
          $isInSubgroup = true;
          break;
        }
      }

      if (!$isInSubgroup) {
        $subgroup->group_name = $subgroup->group()->name;

        $subgroup->view_subgroup = ['href' => 'api/v1/management/subgroups/' . $subgroup->id, 'method' => 'GET'];

        $subgroupsToReturn[] = $subgroup;
      }
    }

    return response()->json(['msg' => 'List of free subgroups', 'subgroups' => $subgroupsToReturn], 200);
  }
}
