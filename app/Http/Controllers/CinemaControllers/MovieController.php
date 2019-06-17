<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Movie;
use App\Models\Cinema\MovieYear;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use stdClass;

class MovieController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function getAll() {
    $toReturnMovies = array();

    $movies = Movie::orderBy('date')->get();
    foreach ($movies as $movie) {
      $returnable = $movie->getReturnable();

      $bookings = array();
      foreach ($movie->moviesBookings() as $moviesBooking) {
        $booking = new stdClass();
        $booking->firstname = $moviesBooking->user()->firstname;
        $booking->surname = $moviesBooking->user()->surname;
        $booking->amount = $moviesBooking->amount;
        $bookings[] = $booking;
      }
      $returnable->bookings = $bookings;

      $returnable->view_movie = ['href' => 'api/v1/cinema/administration/movie/' . $movie->id, 'method' => 'GET'];
      $toReturnMovies[] = $returnable;
    }

    $response = ['msg' => 'List of all movies', 'movies' => $toReturnMovies];

    return response()->json($response);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @return Response
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, ['name' => 'required|max:190|min:1', 'date' => 'required|date', 'trailerLink' => 'required|max:190|min:1', 'posterLink' => 'required|max:190|min:1', 'bookedTickets' => 'integer', 'movie_year_id' => 'required|integer']);

    $name = $request->input('name');
    $date = $request->input('date');
    $trailerLink = $request->input('trailerLink');
    $posterLink = $request->input('posterLink');
    $bookedTickets = $request->input('bookedTickets');
    $movie_year_id = $request->input('movie_year_id');

    if (MovieYear::find($movie_year_id) == null) {
      return response()->json(['msg' => 'Movie year does not exist'], 404);
    }

    $movie = new Movie(['name' => $name, 'date' => $date, 'trailerLink' => $trailerLink, 'posterLink' => $posterLink, 'bookedTickets' => $bookedTickets, 'movie_year_id' => $movie_year_id]);

    if ($movie->save()) {
      $returnable = $movie->getReturnable();
      $returnable->view_movie = ['href' => 'api/v1/cinema/administration/movie/' . $movie->id, 'method' => 'GET'];

      $response = ['msg' => 'Movie created', 'movie' => $returnable];

      return response()->json($response, 201);
    }

    $response = ['msg' => 'An error occurred'];

    return response()->json($response, 404);
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return Response
   */
  public function getSingle($id) {
    $movie = Movie::find($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $returnable = $movie->getReturnable();

    $bookings = array();
    foreach ($movie->moviesBookings() as $moviesBooking) {
      $booking = new stdClass();
      $booking->firstname = $moviesBooking->user()->firstname;
      $booking->surname = $moviesBooking->user()->surname;
      $booking->amount = $moviesBooking->amount;
      $bookings[] = $booking;
    }
    $returnable->bookings = $bookings;

    $returnable->view_movies = ['href' => 'api/v1/cinema/administration/movie', 'method' => 'GET'];

    $response = ['msg' => 'Movie information', 'movie' => $movie];
    return response()->json($response);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param int $id
   * @return Response
   * @throws ValidationException
   */
  public function update(Request $request, $id) {
    $this->validate($request, ['name' => 'required|max:190|min:1', 'date' => 'required|date', 'trailerLink' => 'required|max:190|min:1', 'posterLink' => 'required|max:190|min:1', 'bookedTickets' => 'integer', 'movie_year_id' => 'required|integer']);

    $name = $request->input('name');
    $date = $request->input('date');
    $trailerLink = $request->input('trailerLink');
    $posterLink = $request->input('posterLink');
    $bookedTickets = $request->input('bookedTickets');
    $movie_year_id = $request->input('movie_year_id');

    $movie = Movie::find($id);

    if ($movie == null) {
      return response()->json(['msg' => 'Movie does not exist'], 404);
    }

    if (MovieYear::find($movie_year_id) == null) {
      return response()->json(['msg' => 'Movie year does not exist'], 404);
    }

    $movie->name = $name;
    $movie->date = $date;
    $movie->trailerLink = $trailerLink;
    $movie->posterLink = $posterLink;
    $movie->bookedTickets = $bookedTickets;
    $movie->movie_year_id = $movie_year_id;

    if ($movie->save()) {
      $returnable = $movie->getReturnable();
      $returnable->view_movie = ['href' => 'api/v1/cinema/administration/movie/' . $movie->id, 'method' => 'GET'];

      $response = ['msg' => 'Movie updated', 'movie' => $returnable];

      return response()->json($response, 201);
    }

    $response = ['msg' => 'An error occurred'];

    return response()->json($response, 404);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return Response
   */
  public function delete($id) {
    $movie = Movie::find($id);
    if ($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    if (!$movie->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    $response = ['msg' => 'Movie deleted', 'create' => ['href' => 'api/v1/cinema/administration/movie', 'method' => 'POST', 'params' => 'name, date, trailerLink, posterLink, bookedTickets, movie_year_id']];

    return response()->json($response);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getNotShownMovies(Request $request) {
    $allMovies = Movie::orderBy('date')->get();
    if ($allMovies == null) {
      $response = ['msg' => 'List of not shown movies', 'movies' => $allMovies];

      return response()->json($response);
    }

    $user = $request->auth;

    $movies = [];

    foreach ($allMovies as $movie) {
      if ((time() - (60 * 60 * 24)) < strtotime($movie->date . ' 20:00:00')) {
        $movies[] = $movie;
      }
    }

    $returnableMovies = array();
    foreach ($movies as $movie) {
      $returnable = $movie->getReturnable();

      $movieBookingForYourself = $user->moviesBookings()->where('movie_id', $movie->id)->first();

      if ($movieBookingForYourself == null) {
        $returnable->bookedTicketsForYourself = 0;
      } else {
        $returnable->bookedTicketsForYourself = $movieBookingForYourself->amount;
      }

      $returnable->view_movie = ['href' => 'api/v1/cinema/movie/administration/' . $movie->getAttribute('id'), 'method' => 'GET'];
      $returnableMovies[] = $returnable;
    }

    $response = ['msg' => 'List of not shown movies', 'movies' => $returnableMovies];

    return response()->json($response);
  }
}
