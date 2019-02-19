<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Movie;
use App\Models\Cinema\MoviesBooking;
use Illuminate\Http\Request;

class MovieBookingController extends Controller
{

  /**
   * @param Request $request
   * @return \Illuminate\Http\JsonResponse
   * @throws \Illuminate\Validation\ValidationException
   */
  public function bookTickets(Request $request)
  {
    $this->validate($request, [
      'movie_id' => 'required|numeric',
      'ticketAmount' => 'required|numeric'
    ]);

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
      $movieBooking = new MoviesBooking([
        'user_id' => $user->id,
        'movie_id' => $movie->id,
        'amount' => $ticketAmount
      ]);

      if ($movieBooking->save()) {
        /* If movie booking was successful, update movie booked tickets */
        $movie->bookedTickets += $ticketAmount;
        $movie->save();

        $movieBooking->cancel_booking = [
          'href' => 'api/v1/cinema/booking',
          'params' => 'movie_id',
          'method' => 'DELETE'
        ];

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

      $movieBooking->cancel_booking = [
        'href' => 'api/v1/cinema/booking',
        'params' => 'movie_id',
        'method' => 'DELETE'
      ];

      return response()->json(['msg' => 'Booking successful updated', 'movieBooking' => $movieBooking], 200);
    }

    return response()->json(['msg' => 'An error occurred during updating'], 500);
  }

  public function cancelBooking(Request $request, $id)
  {
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
}
