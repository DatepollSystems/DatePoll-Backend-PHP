<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\Controllers\Controller;
use App\Models\User\User;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Setting\ISettingRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use stdClass;

class UserController extends Controller
{

  protected $settingRepository = null;
  protected $userSettingRepository = null;
  protected $eventRepository = null;

  public function __construct(ISettingRepository $settingRepository, IUserSettingRepository $userSettingRepository, IEventRepository $eventRepository) {
    $this->settingRepository = $settingRepository;
    $this->userSettingRepository = $userSettingRepository;
    $this->eventRepository = $eventRepository;
  }


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
    $this->validate($request, [
      'title' => 'max:190',
      'firstname' => 'required|max:190|min:1',
      'surname' => 'required|max:190|min:1',
      'streetname' => 'required|max:190|min:1',
      'streetnumber' => 'required|max:190|min:1',
      'zipcode' => 'required|integer',
      'location' => 'required|max:190|min:1',
      'birthday' => 'required|date'
    ]);

    $user = $request->auth;

    $user->title = $request->input('title');
    $user->firstname = $request->input('firstname');
    $user->surname = $request->input('surname');
    $user->streetname = $request->input('streetname');
    $user->streetnumber = $request->input('streetnumber');
    $user->zipcode = $request->input('zipcode');
    $user->location = $request->input('location');
    $user->birthday = $request->input('birthday');

    if ($user->save()) {
      $userToShow = $user->getReturnable();

      $userToShow->view_yourself = ['href' => 'api/v1/user/myself', 'method' => 'GET'];

      $response = ['msg' => 'User updated', 'user' => $userToShow];

      return response()->json($response, 201);
    }

    $response = ['msg' => 'An error occurred'];

    return response()->json($response, 500);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function homepage(Request $request) {
    $user = $request->auth;

    $bookingsToShow = array();
    if ($this->settingRepository->getCinemaEnabled()) {
      $bookings = $user->moviesBookings();
      foreach ($bookings as $booking) {
        $movie = $booking->movie();

        if ((time() - (60 * 60 * 24)) < strtotime($movie->date . ' 05:00:00')) {
          $bookingToShow = new stdClass();
          $bookingToShow->movie_id = $movie->id;
          $bookingToShow->movie_name = $movie->name;
          $bookingToShow->movie_date = $movie->date;
          $bookingToShow->amount = $booking->amount;

          if ($movie->worker() == null) {
            $bookingToShow->worker_id = null;
            $bookingToShow->worker_name = null;
          } else {
            $bookingToShow->worker_id = $movie->worker()->id;
            $bookingToShow->worker_name = $movie->worker()->firstname . ' ' . $movie->worker()->surname;
          }

          if ($movie->emergencyWorker() == null) {
            $bookingToShow->emergency_worker_id = null;
            $bookingToShow->emergency_worker_name = null;
          } else {
            $bookingToShow->emergency_worker_id = $movie->emergencyWorker()->id;
            $bookingToShow->emergency_worker_name = $movie->emergencyWorker()->firstname . ' ' . $movie->emergencyWorker()->surname;
          }

          $bookingsToShow[] = $bookingToShow;
        }
      }
    }

    $eventsToShow = array();
    if ($this->settingRepository->getEventsEnabled()) {
      $eventsToShow = $this->eventRepository->getOpenEventsForUser($user);
    }

    $users = User::all();
    $birthdaysToShow = array();
    foreach ($users as $user) {
      if ($this->userSettingRepository->getShareBirthdayForUser($user)) {
        $d = date_parse_from_format("Y-m-d", $user->birthday);
        if ($d["month"] == date('n')) {
          $birthdayToShow = new stdClass();

          $birthdayToShow->name = $user->firstname . ' ' . $user->surname;
          $birthdayToShow->date = $user->birthday;

          $birthdaysToShow[] = $birthdayToShow;
        }
      }
    }

    return response()->json([
      'msg' => 'List of your bookings, events and birthdays in the next month',
      'events' => $eventsToShow,
      'bookings' => $bookingsToShow,
      'birthdays' => $birthdaysToShow], 200);
  }
}
