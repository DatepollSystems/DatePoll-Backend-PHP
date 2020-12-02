<?php

namespace App\Repositories\SeatReservation\UserSeatReservation;

use App\Models\SeatReservation\Place;
use App\Models\SeatReservation\PlaceReservation;
use App\Models\User\User;

interface IUserSeatReservationRepository {

  /**
   * @return PlaceReservation[]
   */
  public function getAllPlaceReservationsOrderedByDate(): array;

  /**
   * @return PlaceReservation[]
   */
  public function getUpcomingPlaceReservationsOrderedByDate(): array;

  /**
   * @param int $id
   * @return PlaceReservation|null
   */
  public function getPlaceReservationById(int $id): ?PlaceReservation;

  /**
   * @param int $userId
   * @return PlaceReservation[]
   */
  public function getUserReservationsByUserId(int $userId): array;

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
  ): ?PlaceReservation;
}
