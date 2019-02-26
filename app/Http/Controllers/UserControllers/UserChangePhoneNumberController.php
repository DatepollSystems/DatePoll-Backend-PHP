<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\UserTelephoneNumber;
use Illuminate\Http\Request;

class UserChangePhoneNumberController extends Controller
{

  /**
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function addPhoneNumber(Request $request) {
    $this->validate($request, [
      'label' => 'required|string|min:1|max:190',
      'number' => 'required|string|min:1|max:190'
    ]);

    $user = $request->auth;

    $phoneNumber = new UserTelephoneNumber([
      'label' => $request->input('label'),
      'number' => $request->input('number'),
      'user_id' => $user->id
    ]);

    if($phoneNumber->save()) {
      return response()->json(['msg' => 'Added phone number', 'phone_number_id' => $phoneNumber->id], 200);
    }

    return response()->json(['msg' => 'An error occurred'], 500);
  }

  public function removePhoneNumber($id) {
    $phoneNumber = UserTelephoneNumber::find($id);
    if($phoneNumber == null) {
      return response()->json(['msg' => 'Phone number not found'], 404);
    }

    if(!$phoneNumber->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    $response = [
      'msg' => 'Phone number deleted',
      'add' => [
        'href' => 'api/v1/user/myself/phoneNumber',
        'method' => 'POST',
        'params' => 'label, number'
      ]
    ];

    return response()->json($response);
  }
}
