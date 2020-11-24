<?php

namespace App\Http\Controllers\SeatReservationControllers;

use App\Http\Controllers\Controller;
use App\Models\SeatReservation\PlaceReservationState;
use App\Permissions;
use App\Repositories\SeatReservation\Place\IPlaceRepository;
use App\Repositories\SeatReservation\UserSeatReservation\IUserSeatReservationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserSeatReservationController extends Controller {
  protected IPlaceRepository $placeRepository;
  protected IUserSeatReservationRepository $userSeatReservationRepository;

  public function __construct(
    IPlaceRepository $placeRepository,
    IUserSeatReservationRepository $userSeatReservationRepository
  ) {
    $this->placeRepository = $placeRepository;
    $this->userSeatReservationRepository = $userSeatReservationRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAllPlaceReservations() {
    $all = $this->userSeatReservationRepository->getAllPlaceReservationsOrderedByDate();
    $reservations = [];
    foreach ($all as $reservation) {
      $reservations[] = $reservation->getReturnable();
    }

    return response()->json(['msg' => 'All place reservations', 'reservations' => $reservations]);
  }

  /**
   * @return JsonResponse
   */
  public function getUpcomingPlaceReservations() {
    $upcoming = $this->userSeatReservationRepository->getUpcomingPlaceReservationsOrderedByDate();
    $reservations = [];
    foreach ($upcoming as $reservation) {
      $reservations[] = $reservation->getReturnable();
    }

    return response()->json(['msg' => 'Upcoming place reservations', 'reservations' => $reservations]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getUserReservations(Request $request) {
    $reservations = [];
    foreach ($this->userSeatReservationRepository->getUserReservationsByUserId($request->auth->id) as $reservation) {
      $reservations[] = $reservation->getReturnable();
    }

    return response()->json(['msg' => 'Your reservations',
      'reservations' => $reservations, ]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'reason' => 'required|string|min:1|max:190',
      'description' => 'nullable|string|max:190',
      'start_date' => 'required|date',
      'end_date' => 'required|date',
      'place_id' => 'required|numeric',
    ]);

    $place = $this->placeRepository->getPlaceById($request->input('place_id'));
    if ($place == null) {
      return response()->json(['msg' => 'Place not found'], 404);
    }
    $reason = $request->input('reason');
    $description = $request->input('description');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');
    $placeReservation = $this->userSeatReservationRepository->createOrUpdatePlaceReservation(
      $reason,
      $description,
      $startDate,
      $endDate,
      PlaceReservationState::WAITING,
      $place,
      $request->auth
    );

    if ($placeReservation == null) {
      return response()->json(['msg' => 'Could not create place reservation'], 500);
    }

    return response()->json(['msg' => 'Successfully created place reservation',
      'place_reservation' => $placeReservation, ], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, int $id) {
    $this->validate($request, [
      'reason' => 'required|string|min:1|max:190',
      'description' => 'nullable|string|max:190',
      'start_date' => 'required|date',
      'end_date' => 'required|date',
      'place_id' => 'required|numeric',
      'state' => 'nullable|string|min:1|max:190|in:WAITING,APPROVED,REJECTED',
    ]);

    $placeReservation = $this->userSeatReservationRepository->getPlaceReservationById($id);
    if ($placeReservation == null) {
      return response()->json(['msg' => 'Place reservation not found'], 404);
    }

    $state = PlaceReservationState::WAITING;
    $approver = null;
    if ($request->auth->hasPermission(Permissions::$SEAT_RESERVATION_ADMINISTRATION)) {
      $state = $request->input('state');
      $approver = $request->auth;
    } else {
      if ($placeReservation->user_id != $request->auth->id) {
        return response()->json(['msg' => 'Insufficient permissions to edit this place reservation'], 403);
      }
      if ($placeReservation->state != PlaceReservationState::WAITING) {
        return response()->json(
          ['msg' => 'You are not allowed to edit this place reservation after approval or declining'],
          400
        );
      }
    }

    $place = $this->placeRepository->getPlaceById($request->input('place_id'));
    if ($place == null) {
      return response()->json(['msg' => 'Place not found'], 404);
    }
    $reason = $request->input('reason');
    $description = $request->input('description');
    $startDate = $request->input('start_date');
    $endDate = $request->input('end_date');

    $placeReservation = $this->userSeatReservationRepository->createOrUpdatePlaceReservation(
      $reason,
      $description,
      $startDate,
      $endDate,
      $state,
      $place,
      null,
      $approver,
      $placeReservation
    );

    if ($placeReservation == null) {
      return response()->json(['msg' => 'Could not save place reservation'], 500);
    }

    return response()->json(['msg' => 'Successfully updated place reservation',
      'place_reservation' => $placeReservation, ], 201);
  }
}
