<?php

namespace App\Http\Controllers\UserControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\System\Setting\ISettingRepository;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserChange\IUserChangeRepository;
use App\Repositories\User\UserSetting\IUserSettingRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use stdClass;

class UserController extends Controller {

  public function __construct(protected IUserRepository $userRepository,
                              protected IUserChangeRepository $userChangeRepository,
                              protected ISettingRepository $settingRepository,
                              protected IEventRepository $eventRepository,
                              protected IBroadcastRepository $broadcastRepository,
                              protected IUserSettingRepository $userSettingRepository) {
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getMyself(AuthenticatedRequest $request): JsonResponse {
    $user = $request->auth;

    return response()->json([
      'msg' => 'Get yourself',
      'user' => $user,], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function updateMyself(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, [
      'title' => 'max:190',
      'streetname' => 'required|max:190|min:1',
      'streetnumber' => 'required|max:190|min:1',
      'zipcode' => 'required|integer',
      'location' => 'required|max:190|min:1',
      'birthday' => 'required|date',]);

    $user = $request->auth;

    $title = $request->input('title');
    $birthday = $request->input('birthday');
    $streetname = $request->input('streetname');
    $streetnumber = $request->input('streetnumber');
    $zipcode = $request->input('zipcode');
    $location = $request->input('location');

    $this->userChangeRepository->checkForPropertyChange('title', $user->id, $user->id, $title, $user->title);
    $this->userChangeRepository->checkForPropertyChange('birthday', $user->id, $user->id, $birthday, $user->birthday);
    $this->userChangeRepository->checkForPropertyChange('streetname', $user->id, $user->id, $streetname, $user->streetname);
    $this->userChangeRepository->checkForPropertyChange('streetnumber', $user->id, $user->id, $streetnumber, $user->streetnumber);
    $this->userChangeRepository->checkForPropertyChange('location', $user->id, $user->id, $location, $user->location);
    // Don't use checkForPropertyChange function because these values aren't strings
    if ($user->zipcode != $zipcode) {
      $this->userChangeRepository->createUserChange('zipcode', $user->id, $user->id, $zipcode, $user->zipcode);
    }

    $user->title = $title;
    $user->streetname = $streetname;
    $user->streetnumber = $streetnumber;
    $user->zipcode = $zipcode;
    $user->location = $location;
    $user->birthday = $birthday;

    if (! $user->save()) {
      return response()->json(['msg' => 'An error occurred'], 500);
    }

    return response()->json([
      'msg' => 'User updated',
      'user' => $user,], 201);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function homepage(AuthenticatedRequest $request): JsonResponse {
    $user = $request->auth;

    $bookingsToShow = [];
    if ($this->settingRepository->getCinemaEnabled()) {
      foreach ($user->moviesBookings() as $booking) {
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

    $eventsToShow = [];
    if ($this->settingRepository->getEventsEnabled()) {
      $eventsToShow = $this->eventRepository->getOpenEventsForUser($user);
    }

    $broadcastsToShow = [];
    if ($this->settingRepository->getBroadcastsEnabled()) {
      $broadcasts = $this->broadcastRepository->getBroadcastsForUserByIdOrderedByDate($user->id, 3);
      foreach ($broadcasts as $broadcast) {
        $broadcastsToShow[] = $this->broadcastRepository->getBroadcastReturnable($broadcast);
      }
    }

    $users = $this->userRepository->getAllUsers();
    $birthdaysToShow = [];
    foreach ($users as $user) {
      if ($this->userSettingRepository->getShareBirthdayForUser($user)) {
        $addTimeDate = date('m-d', strtotime('+15 days', strtotime(date('Y-m-d'))));
        $remTimeDate = date('m-d', strtotime('-1 days', strtotime(date('Y-m-d'))));
        if ($remTimeDate < date('m-d', strtotime($user->birthday)) && date(
            'm-d',
            strtotime($user->birthday)
          ) < $addTimeDate) {
          $birthdayToShow = new stdClass();

          $birthdayToShow->name = $user->getCompleteName();
          $birthdayToShow->date = $user->birthday;

          $birthdaysToShow[] = $birthdayToShow;
        }
      }
    }

    usort($birthdaysToShow, function ($a, $b) {
      return strcmp(date('m-d', strtotime($a->date)), date('m-d', strtotime($b->date)));
    });

    $response = [
      'msg' => 'List of your bookings, events, broadcasts and birthdays in the next month',
      'events' => $eventsToShow,
      'bookings' => $bookingsToShow,
      'broadcasts' => $broadcastsToShow,
      'birthdays' => $birthdaysToShow, ];

    return response()->json($response, 200);
  }
}
