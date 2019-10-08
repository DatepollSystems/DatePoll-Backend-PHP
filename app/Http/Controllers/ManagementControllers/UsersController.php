<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\User\UserPermission;
use App\Permissions;
use App\Repositories\User\User\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class UsersController extends Controller
{

  protected $userRepository;

  public function __construct(IUserRepository $userRepository) {
    $this->userRepository = $userRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function getAll() {
    $toReturnUsers = array();

    $users = $this->userRepository->getAllUsers();
    foreach ($users as $user) {

      $toReturnUser = $user->getReturnable();

      $toReturnUser->view_user = ['href' => 'api/v1/management/users/' . $user->id, 'method' => 'GET'];

      $toReturnUsers[] = $toReturnUser;
    }

    $response = ['msg' => 'List of all users', 'users' => $toReturnUsers];

    return response()->json($response);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @return Response
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'title' => 'max:190',
      'username' => 'required|min:1|max:190',
      'firstname' => 'required|max:190|min:1',
      'surname' => 'required|max:190|min:1',
      'birthday' => 'required|date',
      'join_date' => 'required|date',
      'streetname' => 'required|max:190|min:1',
      'streetnumber' => 'required|max:190|min:1',
      'zipcode' => 'required|integer',
      'location' => 'required|max:190|min:1',
      'activated' => 'required|boolean',
      'activity' => 'required|max:190|min:1',
      'phone_numbers' => 'required|array',
      'permissions' => 'required|array',
      'email_addresses' => 'required|array']);

    $title = $request->input('title');
    $username = $request->input('username');
    $firstname = $request->input('firstname');
    $surname = $request->input('surname');
    $birthday = $request->input('birthday');
    $joinDate = $request->input('join_date');
    $streetname = $request->input('streetname');
    $streetnumber = $request->input('streetnumber');
    $zipcode = $request->input('zipcode');
    $location = $request->input('location');
    $activated = $request->input('activated');
    $activity = $request->input('activity');
    $emailAddresses = $request->input('email_addresses');
    $phoneNumbers = $request->input('phone_numbers');
    $permissions = $request->input('permissions');

    if ($this->userRepository->getUserByUsername($username) != null) {
      return response()->json([
        'msg' => 'The username is already used',
        'error_code' => 'username_already_used'], 400);
    }

    $user = $this->userRepository->createOrUpdateUser($title, $username, $firstname, $surname, $birthday, $joinDate, $streetname, $streetnumber, $zipcode, $location, $activated, $activity, $phoneNumbers, $emailAddresses);

    if ($user == null) {
      return response()->json(['msg' => 'An error occurred during user saving..'], 500);
    }

    if($request->auth->hasPermission(Permissions::$ROOT_ADMINISTRATION) ||
      $request->auth->hasPermission(Permissions::$PERMISSION_ADMINISTRATION)) {
      if($permissions != null) {
        foreach ((array)$permissions as $permission) {
          $permissionToSave = new UserPermission(['permission' => $permission, 'user_id' => $user->id]);
          $permissionToSave->save();
        }
      }
    }

    if ($activated AND $user->hasEmailAddresses()) {
      $this->userRepository->activateUser($user);
    }

    $userToShow = $user->getReturnable();
    $userToShow->view_user = ['href' => 'api/v1/management/users/' . $user->id, 'method' => 'GET'];

    $response = ['msg' => 'User successful created', 'user' => $userToShow];

    return response()->json($response, 201);
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return Response
   */
  public function getSingle($id) {
    $user = $this->userRepository->getUserById($id);
    if ($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $userToShow = $user->getReturnable();

    $userToShow->view_users = ['href' => 'api/v1/management/users', 'method' => 'GET'];

    $response = ['msg' => 'User information', 'user' => $userToShow];
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
    $this->validate($request, [
      'title' => 'max:190',
      'username' => 'required|min:1|max:190',
      'firstname' => 'required|max:190|min:1',
      'surname' => 'required|max:190|min:1',
      'birthday' => 'required|date',
      'join_date' => 'required|date',
      'streetname' => 'required|max:190|min:1',
      'streetnumber' => 'required|max:190|min:1',
      'zipcode' => 'required|integer',
      'location' => 'required|max:190|min:1',
      'activated' => 'required|boolean',
      'activity' => 'required|max:190|min:1',
      'email_addresses' => 'required|array',
      'phone_numbers' => 'required|array',
      'permissions' => 'required|array']);

    $user = $this->userRepository->getUserById($id);
    if ($user == null) {
      return response()->json(['msg' => 'User not found', 'error_code' => 'user_not_found'], 404);
    }

    $username = $request->input('username');

    if ($username != $user->username) {
      if ($this->userRepository->getUserByUsername($username) != null) {
        return response()->json([
          'msg' => 'The username is already used',
          'error_code' => 'username_already_used'], 400);
      }
    }

    $title = $request->input('title');
    $firstname = $request->input('firstname');
    $surname = $request->input('surname');
    $birthday = $request->input('birthday');
    $joinDate = $request->input('join_date');
    $streetname = $request->input('streetname');
    $streetnumber = $request->input('streetnumber');
    $zipcode = $request->input('zipcode');
    $location = $request->input('location');
    $oldActivatedStatus = $user->activated;
    $activated = $request->input('activated');
    $activity = $request->input('activity');
    $emailAddresses = $request->input('email_addresses');
    $phoneNumbers = $request->input('phone_numbers');
    $permissions = $request->input('permissions');

    $user = $this->userRepository->createOrUpdateUser($title, $username, $firstname, $surname, $birthday, $joinDate, $streetname, $streetnumber, $zipcode, $location, $activated, $activity, $phoneNumbers, $emailAddresses, $user);

    if ($user == null) {
      return response()->json(['msg' => 'An error occurred during user saving..'], 500);
    }

    //---------------------------------------------------------------
    //---- Permissions manager only deletes changed permissions -----
    if($request->auth->hasPermission(Permissions::$ROOT_ADMINISTRATION) ||
      $request->auth->hasPermission(Permissions::$PERMISSION_ADMINISTRATION)) {

      $permissionsWhichHaveNotBeenDeleted = array();

      $OldPermissions = $user->permissions();
      foreach ($OldPermissions as $oldPermission) {
        $toDelete = true;

        foreach ((array) $permissions as $permission) {
          if($oldPermission['permission'] == $permission) {
            $toDelete = false;
            $permissionsWhichHaveNotBeenDeleted[] = $permission;
            break;
          }
        }

        if($toDelete) {
          $permissionToDeleteObject = UserPermission::find($oldPermission->id);
          if (!$permissionToDeleteObject->delete()) {
            return response()->json(['msg' => 'Failed during permission clearing...'], 500);
          }
        }
      }

      foreach ((array) $permissions as $permission) {
        $toAdd = true;

        foreach ($permissionsWhichHaveNotBeenDeleted as $permissionWhichHaveNotBeenDeleted) {
          if($permission == $permissionWhichHaveNotBeenDeleted) {
            $toAdd = false;
            break;
          }
        }

        if($toAdd) {
          $permissionToSave = new UserPermission(['permission' => $permission, 'user_id' => $user->id]);
          $permissionToSave->save();
        }
      }
    }
    //---------------------------------------------------------------


    if ($activated AND !$oldActivatedStatus AND $user->hasEmailAddresses()) {
      $this->userRepository->activateUser($user);
    }

    $userToShow = $user->getReturnable();
    $userToShow->view_user = ['href' => 'api/v1/management/users/' . $user->id, 'method' => 'GET'];

    $response = ['msg' => 'User updated', 'user' => $userToShow];

    return response()->json($response, 200);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function changePassword(Request $request, $id) {
    $this->validate($request, [
      'password' => 'required'
    ]);

    $user = $this->userRepository->getUserById($id);
    if ($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    if (!$this->userRepository->changePasswordOfUser($user, $request->input('password'))) {
      return response()->json(['msg' => 'Could not save user'], 500);
    }

    return response()->json(['msg' => 'Saved password from user successfully', 'user' => $user->getReturnable()], 200);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param int $id
   * @return Response
   */
  public function delete(Request $request, $id) {
    $user = $this->userRepository->getUserById($id);
    if ($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    if ($request->auth->id == $id) {
      return response()->json(['msg' => 'Can not delete yourself'], 400);
    }

    if (!$this->userRepository->deleteUser($user)) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    $response = [
      'msg' => 'User deleted',
      'create' => [
        'href' => 'api/v1/management/users',
        'method' => 'POST',
        'params' => 'title, email, firstname, surname, birthday, join_date, streetname, streetnumber, zipcode, location, activated, activity, phoneNumbers']];

    return response()->json($response);
  }

  /**
   * Gives an array of user for export
   *
   * @return JsonResponse
   */
  public function export() {
    $toReturnUsers = $this->userRepository->exportAllUsers();

    return response()->json(['msg' => 'List of users to export', 'users' => $toReturnUsers], 200);
  }

  /**
   * Activates all unactivated users
   *
   * @return JsonResponse
   */
  public function activateAll() {
    $users = $this->userRepository->getAllNotActivatedUsers();

    foreach ($users as $user) {
      if ($user->hasEmailAddresses()) {
        $this->userRepository->activateUser($user);
      }
    }

    return response()->json(['msg' => 'All users have been activated and will receive a mail'], 200);
  }
}