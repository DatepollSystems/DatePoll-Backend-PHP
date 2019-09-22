<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MovieBookingController extends Controller
{

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function bookTickets(Request $request) {
    $this->validate($request, ['movie_id' => 'required|numeric', 'ticketAmount' => 'required|numeric']);

    /* Check if movie exists */
    $movie = Movie::find($request->input('movie_id'));
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $ticketAmount = $request->input('ticketAmount');

    /* Check if there are enough free tickets */
    if (($movie->bookedTickets + $ticketAmount) > 20) {
      $availableTickets = 20 - $movie->bookedTickets;
      return response()->json(['msg' => 'The movie is sold out', 'available_tickets' => $availableTickets], 400);
    }

    $user = $request->auth;

    $movieBooking = MoviesBooking::where('user_id', $user->id)->where('movie_id', $movie->id)->first();

    /* If movie booking doesn't exist, create it */
    if ($movieBooking == null) {
      $movieBooking = new MoviesBooking(['user_id' => $user->id, 'movie_id' => $movie->id, 'amount' => $ticketAmount]);

      if ($movieBooking->save()) {
        /* If movie booking was successful, update movie booked tickets */
        $movie->bookedTickets += $ticketAmount;
        $movie->save();

        $movieBooking->cancel_booking = ['href' => 'api/v1/cinema/booking', 'params' => 'movie_id', 'method' => 'DELETE'];

        return response()->json(['msg' => 'Booking successful created', 'movieBooking' => $movieBooking], 200);
      }

      return response()->json(['msg' => 'An error occurred during booking saving'], 500);
    }

    /* Else update existing booking */
    $movieBooking->amount += $ticketAmount;

    if ($movieBooking->save()) {
      /* If movie booking was successful, update movie booked tickets */
      $movie->bookedTickets += $ticketAmount;
      $movie->save();

      $movieBooking->cancel_booking = ['href' => 'api/v1/cinema/booking', 'params' => 'movie_id', 'method' => 'DELETE'];

      return response()->json(['msg' => 'Booking successful updated', 'movieBooking' => $movieBooking], 200);
    }

    return response()->json(['msg' => 'An error occurred during updating'], 500);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   */
  public function cancelBooking(Request $request, $id) {
    /* Check if movie exists */
    $movie = Movie::find($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $user = $request->auth;

    $movieBooking = MoviesBooking::where('user_id', $user->id)->where('movie_id', $movie->id)->first();

    if ($movieBooking == null) {
      return response()->json(['msg' => 'Booking not found'], 404);
    }

    $ticketsAmount = $movieBooking->amount;
    if ($movieBooking->delete()) {

      $movie->bookedTickets -= $ticketsAmount;
      $movie->save();

      return response()->json(['msg' => 'Booking successful removed'], 200);
    }

    return response()->json(['msg' => 'An error occurred during removing'], 500);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function bookForUsers(Request $request, $id) {
    $movie = Movie::find($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $this->validate($request, ['bookings' => 'required|array']);

    $bookings = (array)$request->input('bookings');

    foreach ($bookings as $booking) {
      $amount = $booking['amount'];
      $user_id = $booking['user_id'];

      $movieBooking = MoviesBooking::where('user_id', '=', $user_id)
                                   ->where('movie_id', '=', $id)
                                   ->first();

      if ($movieBooking == null) {
        $movieBooking = new MoviesBooking([
          'user_id' => $user_id,
          'movie_id' => $id,
          'amount' => $amount]);

        if (!$movieBooking->save()) {
          return response()->json(['msg' => 'An error occurred during booking saving!'], 500);
        }

        $movie->bookedTickets += $amount;
      } else {
        $movieBooking->amount += $amount;

        if (!$movieBooking->save()) {
          return response()->json(['msg' => 'An error occurred during booking saving!'], 500);
        }

        $movie->bookedTickets += $amount;
      }
    }
    $movie->save();

    return response()->json(['msg' => 'Saved selected bookings successfully!'], 201);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function cancelBookingForUsers(Request $request, $id) {
    $movie = Movie::find($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $this->validate($request, ['user_ids' => 'required|array']);

    $userIds = (array)$request->input('user_ids');

    foreach ($userIds as $userId) {
      $movieBooking = MoviesBooking::where('user_id', $userId)
                                   ->where('movie_id', $id)
                                   ->first();

      if ($movieBooking != null) {
        $ticketsAmount = $movieBooking->amount;
        if (!$movieBooking->delete()) {
          return response()->json(['msg' => 'Could not remove booking!'], 500);
        }

        $movie->bookedTickets -= $ticketsAmount;
      }
    }

    $movie->save();

    return response()->json([
      'msg' => 'Removed selected bookings successfully!',
      'available_tickets' => $movie->bookedTickets], 201);
  }
}
