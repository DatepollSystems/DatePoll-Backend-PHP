<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\Cinema\MovieBooking\IMovieBookingRepository;
use App\Repositories\User\User\IUserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MovieBookingController extends Controller
{

  protected $movieBookingRepository = null;
  protected $movieRepository = null;
  protected $userRepository = null;

  public function __construct(IMovieBookingRepository $movieBookingRepository, IMovieRepository $movieRepository, IUserRepository $userRepository) {
    $this->movieBookingRepository = $movieBookingRepository;
    $this->movieRepository = $movieRepository;
    $this->userRepository = $userRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function bookTickets(Request $request) {
    $this->validate($request, [
      'movie_id' => 'required|numeric',
      'ticket_amount' => 'required|int']);

    $user = $request->auth;

    $movie = $this->movieRepository->getMovieById($request->input('movie_id'));
    if ($movie == null) {
      Logging::warning('bookTickets', 'User - ' . $user->id . ' | Movie id - ' . $request->input('movie_id') . ' | Movie not found');
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    $ticketAmount = (int)$request->input('ticket_amount');

    /* Check if there are enough free tickets */
    if (($movie->bookedTickets + $ticketAmount) > 20) {
      $availableTickets = 20 - $movie->bookedTickets;
      return response()->json(['msg' => 'The movie is sold out', 'available_tickets' => $availableTickets, 'error_code' => 'not_enough_available_tickets'], 400);
    }

    $movieBooking = $this->movieBookingRepository->bookTickets($movie, $user, $ticketAmount);
    if ($movieBooking != null) {
      $movie->save();

      $movieBooking->cancel_booking = [
        'href' => 'api/v1/cinema/booking/' . $movie->id,
        'method' => 'DELETE'];

      Logging::info("bookTickets", "Movie booking - " . $movieBooking->id . " | Created");
      return response()->json([
        'msg' => 'Reservation successful',
        'movie_booking' => $movieBooking], 200);

    } else {
      Logging::error("bookTickets", "User - " . $user->id . " | Could not save movie booking");
      return response()->json(['msg' => 'An error occurred during booking saving'], 500);
    }
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function cancelBooking(Request $request, int $id) {
    $user = $request->auth;

    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('bookTickets', 'User - ' . $user->id . ' | Movie id - ' . $request->input('movie_id') . ' | Movie not found');
      return response()->json(['msg' => 'Movie not found', 'error_code' => 'movie_not_found'], 404);
    }

    $movieBooking = $this->movieBookingRepository->cancelBooking($movie, $user);
    if ($movieBooking == null) {
      $movie->save();

      Logging::info("cancelBooking", "Movie booking | Successful");
      return response()->json(['msg' => 'Booking successful removed'], 200);

    } else {
      Logging::error("cancelBooking", "Movie booking - " . $movieBooking->id . " | Could not cancel booking");
      return response()->json(['msg' => 'An error occurred during removing'], 500);
    }
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function bookForUsers(Request $request, int $id) {
    $this->validate($request, ['bookings' => 'required|array']);

    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('bookTickets', 'User - ' . $request->auth->id . ' | Movie id - ' . $request->input('movie_id') . ' | Movie not found');
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $bookings = (array)$request->input('bookings');

    foreach ($bookings as $booking) {
      $ticketAmount = $booking['ticket_amount'];

      $user = $this->userRepository->getUserById($booking['user_id']);
      if ($user == null) {
        Logging::error("bookForUsers", "User - " . $user->id . " | Movie - " . $id . " | User not found");
        return response()->json(['msg' => 'User ' . $user->id . ' not found!'], 404);
      }

      $movieBooking = $this->movieBookingRepository->bookTickets($movie, $user, $ticketAmount);

      if ($movieBooking == null) {
        Logging::error("bookForUsers", "User - " . $user->id . " | Movie - " . $id . " | Could not reserve");
        return response()->json(['msg' => 'An error occurred during booking saving!'], 500);
      }
    }
    $movie->save();

    Logging::info("bookForUsers", "User - " . $request->auth->id . " | Successful");
    return response()->json(['msg' => 'Saved selected bookings successfully!'], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function cancelBookingForUsers(Request $request, int $id) {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $this->validate($request, ['user_ids' => 'required|array']);

    $userIds = (array)$request->input('user_ids');

    foreach ($userIds as $userId) {
      $user = $this->userRepository->getUserById($userId);
      if ($user == null) {
        Logging::error("bookForUsers", "User - " . $user->id . " | Movie - " . $id . " | User not found");
        return response()->json(['msg' => 'User ' . $user->id . ' not found!'], 404);
      }

      if ($this->movieBookingRepository->getMovieBookingByMovieAndUser($movie, $user) != null) {
        $ticketAmount = $this->movieBookingRepository->getMovieBookingByMovieAndUser($movie, $user)->amount;
      } else {
        $ticketAmount = 0;
      }

      $movieBooking = $this->movieBookingRepository->cancelBooking($movie, $user);

      if ($movieBooking != null) {
        Logging::error("cancelBookingForUsers", "Movie booking - " . $movieBooking->id . " | Could not delete");
        return response()->json(['msg' => 'Could not remove booking!'], 500);
      }
    }

    $movie->save();

    Logging::info("cancelBookingForUsers", "User - " . $request->auth->id . " | Successful");
    return response()->json([
      'msg' => 'Removed selected bookings successfully!'], 201);
  }
}
