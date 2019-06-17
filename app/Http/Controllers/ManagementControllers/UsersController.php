<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Models\User\UserEmailAddress;
use App\Models\User\UserPermission;
use App\Models\User\UserTelephoneNumber;
use App\Permissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use stdClass;

class UsersController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function getAll() {
    $toReturnUsers = array();

    $users = User::all();
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
      'phoneNumbers' => 'array',
      'permissions' => 'array',
      'emailAddresses' => 'array']);

    $username = $request->input('username');
    $firstname = $request->input('firstname');
    $surname = $request->input('surname');
    $activated = $request->input('activated');
    $emailAddresses = $request->input('emailAddresses');
    $phoneNumbers = $request->input('phoneNumbers');
    $permissions = $request->input('permissions');

    if (User::where('username', $username)->first() != null) {
      return response()->json([
        'msg' => 'The username is already used',
        'error_code' => 'username_already_used'], 400);
    }

    $user = new User([
      'title' => $request->input('title'),
      'username' => $username,
      'firstname' => $firstname,
      'surname' => $surname,
      'birthday' => $request->input('birthday'),
      'join_date' => $request->input('join_date'),
      'streetname' => $request->input('streetname'),
      'streetnumber' => $request->input('streetnumber'),
      'zipcode' => $request->input('zipcode'),
      'location' => $request->input('location'),
      'activated' => $activated,
      'activity' => $request->input('activity'),
      'password' => 'Null']);

    if (!$user->save()) {
      return response()->json(['msg' => 'An error occurred during user saving..'], 500);
    }

    if ($emailAddresses != null) {
      foreach ((array)$emailAddresses as $emailAddress) {
        $emailAddressToSave = new UserEmailAddress([
          'email' => $emailAddress,
          'user_id' => $user->id]);
        $emailAddressToSave->save();
      }
    }

    if ($phoneNumbers != null) {
      foreach ((array)$phoneNumbers as $phoneNumber) {
        $phoneNumberToSave = new UserTelephoneNumber(['label' => $phoneNumber['label'], 'number' => $phoneNumber['number'], 'user_id' => $user->id]);

        $phoneNumberToSave->save();
      }
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
      $user->activate();
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
    $user = User::find($id);
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
      'emailAddresses' => 'array',
      'phoneNumbers' => 'array',
      'permissions' => 'array']);

    $user = User::find($id);
    if ($user == null) {
      return response()->json(['msg' => 'User not found', 'error_code' => 'user_not_found'], 404);
    }

    $username = $request->input('username');

    if ($username != $user->username) {
      if (User::where('username', $username)->first() != null) {
        return response()->json([
          'msg' => 'The username is already used',
          'error_code' => 'username_already_used'], 400);
      }
    }

    $firstname = $request->input('firstname');
    $surname = $request->input('surname');
    $activated = $request->input('activated');
    $emailAddresses = $request->input('emailAddresses');
    $phoneNumbers = $request->input('phoneNumbers');
    $permissions = $request->input('permissions');

    $user->username = $username;
    $user->title = $request->input('title');
    $user->firstname = $firstname;
    $user->surname = $surname;
    $user->birthday = $request->input('birthday');
    $user->join_date = $request->input('join_date');
    $user->streetname = $request->input('streetname');
    $user->streetnumber = $request->input('streetnumber');
    $user->zipcode = $request->input('zipcode');
    $user->location = $request->input('location');
    $oldActivatedStatus = $user->activated;
    $user->activated = $activated;
    $user->activity = $request->input('activity');

    if (!$user->save()) {
      return response()->json(['msg' => 'An error occurred during user saving..'], 500);
    }

    //----Email addresses manager only deletes changed email addresses---
    $emailAddressesWhichHaveNotBeenDeleted = array();

    $OldEmailAddresses = $user->emailAddresses();
    foreach ($OldEmailAddresses as $oldEmailAddress) {
      $toDelete = true;

      foreach ((array)$emailAddresses as $emailAddress) {
        if ($oldEmailAddress['email'] == $emailAddress) {
          $toDelete = false;
          $emailAddressesWhichHaveNotBeenDeleted[] = $emailAddress;
          break;
        }
      }

      if ($toDelete) {
        $emailAddressToDeleteObject = UserEmailAddress::find($oldEmailAddress->id);
        if (!$emailAddressToDeleteObject->delete()) {
          return response()->json(['msg' => 'Failed during email address clearing...'], 500);
        }
      }
    }

    foreach ((array)$emailAddresses as $emailAddress) {
      $toAdd = true;

      foreach ($emailAddressesWhichHaveNotBeenDeleted as $EmailAddressWhichHasNotBeenDeleted) {
        if ($emailAddress == $EmailAddressWhichHasNotBeenDeleted) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        $emailAddressToSave = new UserEmailAddress([
          'email' => $emailAddress,
          'user_id' => $user->id]);

        $emailAddressToSave->save();
      }
    }
    //---------------------------------------------------------------
    //----Phone numbers manager only deletes changed phone numbers---
    $phoneNumbersWhichHaveNotBeenDeleted = array();

    $OldPhoneNumbers = $user->telephoneNumbers();
    foreach ($OldPhoneNumbers as $oldPhoneNumber) {
      $toDelete = true;

      foreach ((array)$phoneNumbers as $phoneNumber) {
        if ($oldPhoneNumber['label'] == $phoneNumber['label'] AND $oldPhoneNumber['number'] == $phoneNumber['number']) {
          $toDelete = false;
          $phoneNumbersWhichHaveNotBeenDeleted[] = $phoneNumber;
          break;
        }
      }

      if ($toDelete) {
        $phoneNumberToDeleteObject = UserTelephoneNumber::find($oldPhoneNumber->id);
        if (!$phoneNumberToDeleteObject->delete()) {
          return response()->json(['msg' => 'Failed during telephone number clearing...'], 500);
        }
      }
    }

    foreach ((array)$phoneNumbers as $phoneNumber) {
      $toAdd = true;

      foreach ($phoneNumbersWhichHaveNotBeenDeleted as $phoneNumberWhichHasNotBeenDeleted) {
        if ($phoneNumber['label'] == $phoneNumberWhichHasNotBeenDeleted['label'] AND $phoneNumber['number'] == $phoneNumberWhichHasNotBeenDeleted['number']) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        $phoneNumberToSave = new UserTelephoneNumber(['label' => $phoneNumber['label'], 'number' => $phoneNumber['number'], 'user_id' => $user->id]);

        $phoneNumberToSave->save();
      }
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
      $user->activate();
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

    $user = User::find($id);
    if ($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $user->password = app('hash')->make($request->input('password') . $user->id);
    if (!$user->save()) {
      return response()->json(['msg' => 'Could not save user'], 500);
    }

    return response()->json(['msg' => 'Saved password from user successfully', 'user' => $user->getReturnable()], 200);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return Response
   */
  public function delete(Request $request, $id) {
    $user = User::find($id);
    if ($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    if ($request->auth->id == $id) {
      return response()->json(['msg' => 'Can not delete yourself'], 400);
    }

    if (!$user->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    $response = ['msg' => 'User deleted', 'create' => ['href' => 'api/v1/management/users', 'method' => 'POST', 'params' => 'title, email, firstname, surname, birthday, join_date, streetname, streetnumber, zipcode, location, activated, activity, phoneNumbers']];

    return response()->json($response);
  }

  /**
   * Gives an array of user for export
   *
   * @return JsonResponse
   */
  public function export() {
    $toReturnUsers = array();

    $users = User::all();
    foreach ($users as $user) {

      $toReturnUser = new stdClass();

      $toReturnUser->Email = '';

      $emailAddresses = $user->emailAddresses();
      foreach ($emailAddresses as $emailAddress) {
        $toReturnUser->Email = $emailAddress['email'] . ';';
      }

      $toReturnUser->Titel = $user->title;
      $toReturnUser->Vorname = $user->firstname;
      $toReturnUser->Nachname = $user->surname;
      $toReturnUser->Geburtstag = $user->birthday;
      $toReturnUser->Beitrittsdatum = $user->join_date;
      $toReturnUser->Straßenname = $user->streetname;
      $toReturnUser->Hausnummer = $user->streetnumber;
      $toReturnUser->Postleitzahl = $user->zipcode;
      $toReturnUser->Ortsname = $user->location;
      $toReturnUser->Aktivitaet = $user->activity;

      $telephoneNumbers = '';
      foreach ($user->telephoneNumbers() as $telephoneNumber) {
        $telephoneNumbers .= $telephoneNumber->number . ', ';
      }

      $toReturnUser->Telefonnummern = $telephoneNumbers;

      $groups = '';
      foreach ($user->usersMemberOfGroups() as $usersMemberOfGroup) {
        $groups .= $usersMemberOfGroup->group()->name . ', ';
      }
      $toReturnUser->Gruppen = $groups;

      $subgroups = '';
      foreach ($user->usersMemberOfSubgroups() as $usersMemberOfSubgroup) {
        $subgroups .= $usersMemberOfSubgroup->subgroup()->group()->name . ' - ' . $usersMemberOfSubgroup->subgroup()->name . ', ';
      }
      $toReturnUser->Register = $subgroups;

      $performanceBadgeForUser = '';
      foreach($user->performanceBadges() as $performanceBadge) {
        $performanceBadgeForUser .= $performanceBadge->instrument()->name . ': ' . $performanceBadge->performanceBadge()->name;
        if($performanceBadge->date != '1970-01-01') {
          $performanceBadgeForUser .= ' am ' . $performanceBadge->date;
        }
        if($performanceBadge->grade != null) {
          $performanceBadgeForUser .= ' mit ' . $performanceBadge->grade . ' Erfolg';
        }
        $performanceBadgeForUser .= '; ';
      }
      $toReturnUser->Leistungsabzeichen = $performanceBadgeForUser;

      $toReturnUsers[] = $toReturnUser;
    }

    return response()->json(['msg' => 'List of users to export', 'users' => $toReturnUsers], 200);

  }

  /**
   * Activates all unactivated users
   *
   * @return JsonResponse
   */
  public function activateAll() {
    $users = User::where('activated', 0)->get();

    foreach ($users as $user) {
      if ($user->hasEmailAddresses()) {
        $user->activate();
      }
    }

    return response()->json(['msg' => 'All users have been activated and will receive a mail'], 200);
  }
}