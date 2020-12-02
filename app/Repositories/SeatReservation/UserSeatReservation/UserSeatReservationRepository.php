<?php

namespace App\Repositories\SeatReservation\UserSeatReservation;

use App\Logging;
use App\Models\SeatReservation\Place;
use App\Models\SeatReservation\PlaceReservation;
use App\Models\User\User;
use Illuminate\Support\Facades\DB;

class UserSeatReservationRepository implements IUserSeatReservationRepository {

  /**
   * @return PlaceReservation[]
   */
  public function getAllPlaceReservationsOrderedByDate(): array {
    return PlaceReservation::orderBy('start_date')->get()->all();
  }

  /**
   * @return PlaceReservation[]
   */
  public function getUpcomingPlaceReservationsOrderedByDate(): array {
    return DB::table('places_reservations_by_users')->where(
      'start_date',
      '>',
      date('Y-m-d H:i:s')
    )->orderBy('start_date')->get()->all();
  }

  /**
   * @param int $id
   * @return PlaceReservation|null
   */
  public function getPlaceReservationById(int $id): ?PlaceReservation {
    return PlaceReservation::find($id);
  }

  /**
   * @param int $userId
   * @return PlaceReservation[]
   */
  public function getUserReservationsByUserId(int $userId): array {
    return PlaceReservation::where('user_id', '=', $userId)->get()->all();
  }

  /**
   * @param string $reason
   * @param string $description
   * @param string $startDate
   * @param string $endDate
   * @param string $state
   * @param Place $place
   * @param User|null $user
   * @param User|null $approver
   * @param PlaceReservation|null $placeReservation
   * @return PlaceReservation|null
   */
  public function createOrUpdatePlaceReservation(
    string $reason,
    string $description,
    string $startDate,
    string $endDate,
    string $state,
    Place $place,
    ?User $user = null,
    ?User $approver = null,
    ?PlaceReservation $placeReservation = null
  ): ?PlaceReservation {
    if ($placeReservation == null && $user != null) {
      $placeReservation = new PlaceReservation(['reason' => $reason, 'description' => $description,
        'start_date' => $startDate, 'end_date' => $endDate,
        'state' => $state, 'place_id' => $place->id, 'user_id' => $user->id, ]);
    } else {
      $placeReservation->reason = $reason;
      $placeReservation->description = $description;
      $placeReservation->start_date = $startDate;
      $placeReservation->end_date = $endDate;
      $placeReservation->state = $state;
    }

    if ($approver != null) {
      $placeReservation->approver_id = $approver->id;
    }

    if (! $placeReservation->save()) {
      Logging::error('createOrUpdatePlaceReservation', 'Could not save place reservation!');

      return null;
    }

    return $placeReservation;
  }
}
