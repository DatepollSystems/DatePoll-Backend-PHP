<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class UserController extends Controller
{

  public function getMyself(Request $request)
  {
    $user = $request->auth;

    $toReturnUser = new \stdClass();

    $toReturnUser->id = $user->id;
    $toReturnUser->email = $user->email;
    $toReturnUser->title = $user->title;
    $toReturnUser->firstname = $user->firstname;
    $toReturnUser->surname = $user->surname;
    $toReturnUser->birthday = $user->birthday;
    $toReturnUser->join_date = $user->join_date;
    $toReturnUser->streetname = $user->streetname;
    $toReturnUser->streetnumber = $user->streetnumber;
    $toReturnUser->zipcode = $user->zipcode;
    $toReturnUser->location = $user->location;
    $toReturnUser->activity = $user->activity;

    $userPermissions = DB::table('user_permissions')->where('user_id', '=', $user->id)->get();
    $permissions = array();
    foreach ($userPermissions as $permission) {
      $permissions[] = $permission->permission;
    }

    $toReturnUser->permissions = $permissions;

    $userTelephoneNumbers = DB::table('user_telephone_numbers')->where('user_id', '=', $user->id)->get();
    $telephoneNumbers = array();
    foreach ($userTelephoneNumbers as $telephoneNumber) {
      $telephoneNumbers[] = [
        'id' => $telephoneNumber->id,
        'number' => $telephoneNumber->number,
        'label' => $telephoneNumber->label
      ];
    }

    $toReturnUser->telephoneNumbers = $telephoneNumbers;

    return response()->json(['msg' => 'Get yourself', 'user' => $toReturnUser], 200);
  }

  /**
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function updateMyself(Request $request)
  {
    $this->validate($request, [
      'firstname' => 'required|max:255|min:1',
      'surname' => 'required|max:255|min:1',
      'streetname' => 'required|max:255|min:1',
      'streetnumber' => 'required|max:255|min:1',
      'zipcode' => 'required|integer',
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

    if ($user->save()) {
      $user->view_yourself = [
        'href' => 'api/v1/user/yourself',
        'method' => 'GET'
      ];

      $user->password = null;
      $user->remember_token = null;
      $user->force_password_change = null;
      $user->email_verified = null;

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

  public function homepage(Request $request) {
    $user = $request->auth;

    $bookings = $user->moviesBookings();
    $bookingsToShow = array();
    foreach ($bookings as $booking) {
      $movie = $booking->movie();

      $bookingToShow = new \stdClass();
      $bookingToShow->movieID = $movie->id;
      $bookingToShow->movieName = $movie->name;
      $bookingToShow->movieDate = $movie->date;
      $bookingToShow->amount = $booking->amount;

      if ($movie->worker() == null) {
        $bookingToShow->workerName = null;
      } else {
        $bookingToShow->workerName = $movie->worker()->firstname . ' ' . $movie->worker()->surname;
      }

      if ($movie->emergencyWorker() == null) {
        $bookingToShow->emergencyWorkerName = null;
      } else {
        $bookingToShow->emergencyWorkerName = $movie->emergencyWorker()->firstname . ' ' . $movie->emergencyWorker()->surname;
      }

      $bookingsToShow[] = $bookingToShow;
    }

    $users = User::all();
    $birthdaysToShow = array();
    foreach ($users as $user) {
      $d = date_parse_from_format("Y-m-d", $user->birthday);
      if ($d["month"] == date('n')) {
        $birthdayToShow = new \stdClass();

        $birthdayToShow->name = $user->firstname . ' ' . $user->surname;
        $birthdayToShow->date = $user->birthday;

        $birthdaysToShow[] = $birthdayToShow;
      }
    }

    return response()->json(['msg' => 'List of your bookings and birthdays in the next month', 'bookings' => $bookingsToShow, 'birthdays' => $birthdaysToShow], 200);
  }
}
