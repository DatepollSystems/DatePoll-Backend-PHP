<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use stdClass;

class UserController extends Controller
{

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getMyself(Request $request) {
    $user = $request->auth;

    $toReturnUser = $user->getReturnable();

    return response()->json(['msg' => 'Get yourself', 'user' => $toReturnUser], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function updateMyself(Request $request) {
    $this->validate($request, ['firstname' => 'required|max:190|min:1', 'surname' => 'required|max:190|min:1', 'streetname' => 'required|max:190|min:1', 'streetnumber' => 'required|max:190|min:1', 'zipcode' => 'required|integer', 'location' => 'required|max:190|min:1', 'birthday' => 'required|date']);

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
      $userToShow = $user->getReturnable();

      $userToShow->view_yourself = ['href' => 'api/v1/user/yourself', 'method' => 'GET'];

      $response = ['msg' => 'User updated', 'user' => $userToShow];

      return response()->json($response, 201);
    }

    $response = ['msg' => 'An error occurred'];

    return response()->json($response, 404);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function homepage(Request $request) {
    $user = $request->auth;

    $bookingsToShow = array();
    if (env('APP_CINEMA_ENABLED', false)) {
      $bookings = $user->moviesBookings();
      foreach ($bookings as $booking) {
        $movie = $booking->movie();

        if ((time() - (60 * 60 * 24)) < strtotime($movie->date . ' 20:00:00')) {
          $bookingToShow = new stdClass();
          $bookingToShow->movieID = $movie->id;
          $bookingToShow->movieName = $movie->name;
          $bookingToShow->movieDate = $movie->date;
          $bookingToShow->amount = $booking->amount;

          if ($movie->worker() == null) {
            $bookingToShow->workerID = null;
            $bookingToShow->workerName = null;
          } else {
            $bookingToShow->workerID = $movie->worker()->id;
            $bookingToShow->workerName = $movie->worker()->firstname . ' ' . $movie->worker()->surname;
          }

          if ($movie->emergencyWorker() == null) {
            $bookingToShow->emergencyWorkerID = null;
            $bookingToShow->emergencyWorkerName = null;
          } else {
            $bookingToShow->emergencyWorkerID = $movie->emergencyWorker()->id;
            $bookingToShow->emergencyWorkerName = $movie->emergencyWorker()->firstname . ' ' . $movie->emergencyWorker()->surname;
          }

          $bookingsToShow[] = $bookingToShow;
        }
      }
    }

    $eventsToShow = array();
    if (env('APP_EVENTS_ENABLED', false)) {
      $eventsToShow = $user->getOpenEvents();
    }

    $users = User::all();
    $birthdaysToShow = array();
    foreach ($users as $user) {
      $d = date_parse_from_format("Y-m-d", $user->birthday);
      if ($d["month"] == date('n')) {
        $birthdayToShow = new stdClass();

        $birthdayToShow->name = $user->firstname . ' ' . $user->surname;
        $birthdayToShow->date = $user->birthday;

        $birthdaysToShow[] = $birthdayToShow;
      }
    }

    return response()->json([
      'msg' => 'List of your bookings, events and birthdays in the next month',
      'events' => $eventsToShow,
      'bookings' => $bookingsToShow,
      'birthdays' => $birthdaysToShow], 200);
  }
}
