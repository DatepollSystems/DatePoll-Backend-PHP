<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Movie;
use Illuminate\Http\Request;

class MovieWorkerController extends Controller
{

  public function applyForWorker(Request $request, $id) {
    /* Check if movie exists */
    $movie = Movie::find($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    if ($movie->worker() != null) {
      return response()->json(['msg' => 'Worker already applied for this movie'], 400);
    }

    $user = $request->auth;

    $movie->worker_id = $user->id;

    if ($movie->save()) {
      return response()->json(['msg' => 'Successfully applied for worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during applying'], 500);
  }

  public function signOutForWorker(Request $request, $id) {
    /* Check if movie exists */
    $movie = Movie::find($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    if($movie->worker() == null) {
      return response()->json(['msg' => 'No worker found for this movie'], 400);
    }

    $user = $request->auth;

    if($movie->worker()->id != $user->id) {
      return response()->json(['msg' => 'You are not the worker for this movie'], 400);
    }

    $movie->worker_id = null;
    if ($movie->save()) {
      return response()->json(['msg' => 'Successfully signed out for worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during signing out'], 500);
  }

  public function applyForEmergencyWorker(Request $request, $id) {
    /* Check if movie exists */
    $movie = Movie::find($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    if ($movie->emergencyWorker() != null) {
      return response()->json(['msg' => 'Emergency worker already applied for this movie'], 400);
    }

    $user = $request->auth;

    $movie->emergency_worker_id = $user->id;

    if ($movie->save()) {
      return response()->json(['msg' => 'Successfully applied for emergency worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during applying'], 500);
  }

  public function signOutForEmergencyWorker(Request $request, $id) {
    /* Check if movie exists */
    $movie = Movie::find($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    if($movie->emergencyWorker() == null) {
      return response()->json(['msg' => 'No emergency worker found for this movie'], 400);
    }

    $user = $request->auth;

    if($movie->emergencyWorker()->id != $user->id) {
      return response()->json(['msg' => 'You are not the emergency worker for this movie'], 400);
    }

    $movie->emergency_worker_id = null;
    if ($movie->save()) {
      return response()->json(['msg' => 'Successfully signed out for emergency worker'], 200);
    }

    return response()->json(['msg' => 'An error occurred during signing out'], 500);
  }

  public function getMovies(Request $request) {
    $user = $request->auth;

    $movies = array();
    $moviesIDs = array();

    foreach ($user->workerMovies() as $movie) {
      if((time()-(60*60*24)) < strtotime($movie->date. ' 20:00:00')) {
        $moviesIDs[] = $movie->id;

        $localMovie = new \stdClass();
        $localMovie->movieName = $movie->name;
        $localMovie->movieID = $movie->id;
        $localMovie->date = $movie->date;

        $orders = array();
        foreach ($movie->moviesBookings() as $moviesBooking) {
          $localBooking = new \stdClass();
          $bookingUser = $moviesBooking->user();
          $localBooking->userName = $bookingUser->firstname . ' ' . $bookingUser->surname;
          $localBooking->userID = $bookingUser->id;
          $localBooking->amount = $moviesBooking->amount;
          $orders[] = $localBooking;
        }
        $localMovie->orders = $orders;

        $movies[] = $localMovie;
      }
    }

    foreach ($user->emergencyWorkerMovies() as $movie) {
      if((time()-(60*60*24)) < strtotime($movie->date. ' 20:00:00')) {
        if (!in_array($movie->id, $moviesIDs)) {
          $localMovie = new \stdClass();
          $localMovie->movieName = $movie->name;
          $localMovie->movieID = $movie->id;
          $localMovie->date = $movie->date;

          $orders = array();
          foreach ($movie->moviesBookings() as $moviesBooking) {
            $localBooking = new \stdClass();
            $bookingUser = $moviesBooking->user();
            $localBooking->userName = $bookingUser->firstname . ' ' . $bookingUser->surname;
            $localBooking->userID = $bookingUser->id;
            $localBooking->amount = $moviesBooking->amount;
            $orders[] = $localBooking;
          }
          $localMovie->orders = $orders;

          $movies[] = $localMovie;
        }
      }
    }

    return response()->json(['msg' => 'Booked tickets for your movie service', 'movies' => $movies], 200);
  }
}
