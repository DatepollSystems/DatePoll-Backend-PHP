<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    public function getMyself(Request $request) {
      $user = $request->auth;

      return response()->json($user, 200);
    }

    public function updateMyself(Request $request) {
      $this->validate($request, [
        'firstname' => 'required|max:255|min:1',
        'surname' => 'required|max:255|min:1',
        'streetname' => 'required|max:255|min:1',
        'streetnumber' => 'required|max:255|min:1',
        'zipcode' => 'required|max:255|min:1',
        'location' => 'required|max:255|min:1',
        'birthday' => 'required|date'
      ]);

      $user = $request->auth;

      $title = $request->input('title');
      $firstname = $request->input('firstname');
      $surname = $request->input('surname');
      $streetname = $request->input('streetname');
      $streetnumber = $request->input('streetnumber');
      $zipcode = $request->input('zipcode');
      $location = $request->input('location');
      $birthday = $request->input('birthday');

      $user->title = $title;
      $user->firstname = $firstname;
      $user->surname = $surname;
      $user->streetname = $streetname;
      $user->streetnumber = $streetnumber;
      $user->zipcode = $zipcode;
      $user->location = $location;
      $user->birthday = $birthday;

      if($user->save()) {
        $user->view_yourself = [
          'href' => 'api/v1/user/yourself',
          'method' => 'GET'
        ];

        $response = [
          'msg' => 'User updated',
          'user' => $user
        ];

        return response()->json($response, 201);
      }

      $response = [
        'msg' => 'An error occurred'
      ];

      return response()->json($response, 404);
    }
}
