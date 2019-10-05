<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Models\User\User;
use App\Repositories\Cinema\Movie\IMovieRepository;
use App\Repositories\Cinema\MovieBooking\IMovieBookingRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MovieBookingController extends Controller
{

  protected $movieBookingRepository = null;
  protected $movieRepository = null;

  public function __construct(IMovieBookingRepository $movieBookingRepository, IMovieRepository $movieRepository) {
    $this->movieBookingRepository = $movieBookingRepository;
    $this->movieRepository = $movieRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function bookTickets(Request $request) {
    $this->validate($request, [
      'movie_id' => 'required|numeric',
      'ticket_amount' => 'required|numeric']);

    $user = $request->auth;

    $movie = $this->movieRepository->getMovieById($request->input('movie_id'));
    if ($movie == null) {
      Logging::warning('bookTickets', 'User - ' . $user->id . ' | Movie id - ' . $request->input('movie_id') . ' | Movie not found');
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $ticketAmount = $request->input('ticket_amount');

    /* Check if there are enough free tickets */
    if (($movie->bookedTickets + $ticketAmount) > 20) {
      $availableTickets = 20 - $movie->bookedTickets;
      return response()->json(['msg' => 'The movie is sold out', 'available_tickets' => $availableTickets], 400);
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
        'movieBooking' => $movieBooking], 200);

    } else {
      Logging::error("bookTickets", "User - " . $user->id . " | Could not save movie booking");
      return response()->json(['msg' => 'An error occurred during booking saving'], 500);
    }
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   * @throws Exception
   */
  public function cancelBooking(Request $request, $id) {
    $user = $request->auth;

    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('bookTickets', 'User - ' . $user->id . ' | Movie id - ' . $request->input('movie_id') . ' | Movie not found');
      return response()->json(['msg' => 'Movie not found'], 404);
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
   * @param $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function bookForUsers(Request $request, $id) {
    $this->validate($request, ['bookings' => 'required|array']);

    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      Logging::warning('bookTickets', 'User - ' . $request->auth->id . ' | Movie id - ' . $request->input('movie_id') . ' | Movie not found');
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $bookings = (array)$request->input('bookings');

    foreach ($bookings as $booking) {
      $ticketAmount = $booking['ticket_amount'];

      // TODO: user repository
      $user = User::find($booking['user_id']);
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
   * @param $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function cancelBookingForUsers(Request $request, $id) {
    $movie = $this->movieRepository->getMovieById($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $this->validate($request, ['user_ids' => 'required|array']);

    $userIds = (array)$request->input('user_ids');

    foreach ($userIds as $userId) {
      // TODO: add user repository
      $user = User::find($userId);
      if ($user == null) {
        Logging::error("bookForUsers", "User - " . $user->id . " | Movie - " . $id . " | User not found");
        return response()->json(['msg' => 'User ' . $user->id . ' not found!'], 404);
      }

      $ticketAmount = $this->movieBookingRepository->getMovieBookingByMovieAndUser($movie, $user)->amount;

      $movieBooking = $this->movieBookingRepository->cancelBooking($movie, $user);

      if ($movieBooking != null) {
        Logging::error("cancelBookingForUsers", "Movie booking - " . $movieBooking->id . " | Could not delete");
        return response()->json(['msg' => 'Could not remove booking!'], 500);
      }

      $movie->bookedTickets -= $ticketAmount;
    }

    $movie->save();

    Logging::info("cancelBookingForUsers", "User - " . $request->auth->id . " | Successful");
    return response()->json([
      'msg' => 'Removed selected bookings successfully!',
      'available_tickets' => $movie->bookedTickets], 201);
  }
}
