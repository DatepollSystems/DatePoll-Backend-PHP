<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\User\UserEmailAddress;
use Illuminate\Http\Request;

class UserChangeEmailController extends Controller
{

  public function changeEmailAddresses(Request $request) {
    $this->validate($request, ['email_addresses' => 'required|array']);

    $user = $request->auth;

    $emailAddresses = $request->input('email_addresses');

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

    return response()->json([
      'msg' => 'All email addresses saved',
      'user' => $user->getReturnable()], 200);
  }
}
