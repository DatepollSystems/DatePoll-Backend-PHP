<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Models\Cinema\Movie;
use App\Models\Cinema\MovieYear;
use App\Models\User;
use Illuminate\Http\Request;

class MovieController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function getAll()
  {
    $movies = Movie::all();
    foreach ($movies as $movie) {
      $workerID = $movie->worker_id;
      $emergencyWorkerID = $movie->emergency_worker_id;

      $worker = User::find($workerID);
      $emergencyWorker = User::find($emergencyWorkerID);

      if($worker == null) {
        $movie->workerID = null;
        $movie->workerName = '-';
      } else {
        $movie->workerID = $worker->id;
        $movie->workerName = $worker->getAttribute('firstname') . ' ' . $worker->getAttribute('surname');
      }

      if($emergencyWorker == null) {
        $movie->emergencyWorkerID = null;
        $movie->emergencyWorkerName = '-';
      } else {
        $movie->emergencyWorkerID = $emergencyWorker->id;
        $movie->emergencyWorkerName = $emergencyWorker->getAttribute('firstname') . ' ' . $emergencyWorker->getAttribute('surname');
      }

      $movie->view_movie = [
        'href' => 'api/v1/cinema/movie/'.$movie->getAttribute('id'),
        'method' => 'GET'
      ];
    }

    $response = [
      'msg' => 'List of all movies',
      'movies' => $movies
    ];

    return response()->json($response);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @return \Illuminate\Http\Response
   * @throws \Illuminate\Validation\ValidationException
   */
  public function create(Request $request)
  {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'date' => 'required|date',
      'trailerLink' => 'required|max:190|min:1',
      'posterLink' => 'required|max:190|min:1',
      'bookedTickets' => 'integer',
      'movie_year_id' => 'required|integer'
    ]);

    $name = $request->input('name');
    $date = $request->input('date');
    $trailerLink = $request->input('trailerLink');
    $posterLink = $request->input('posterLink');
    $bookedTickets = $request->input('bookedTickets');
    $movie_year_id = $request->input('movie_year_id');

    if(MovieYear::find($movie_year_id) == null) {
      return response()->json(['msg' => 'Movie year does not exist'], 404);
    }

    $movie = new Movie([
      'name' => $name,
      'date' => $date,
      'trailerLink' => $trailerLink,
      'posterLink' => $posterLink,
      'bookedTickets' => $bookedTickets,
      'movie_year_id' => $movie_year_id
    ]);

    if($movie->save()) {
      $movie->view_movie = [
        'href' => 'api/v1/cinema/movie/'.$movie->getAttribute('id'),
        'method' => 'GET'
      ];

      $response = [
        'msg' => 'Movie created',
        'movie' => $movie
      ];

      return response()->json($response, 201);
    }

    $response = [
      'msg' => 'An error occurred'
    ];

    return response()->json($response, 404);
  }

  /**
   * Display the specified resource.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function getSingle($id)
  {
    $movie = Movie::find($id);
    if($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    $workerID = $movie->worker_id;
    $emergencyWorkerID = $movie->emergency_worker_id;

    $worker = User::find($workerID);
    $emergencyWorker = User::find($emergencyWorkerID);

    if($worker == null) {
      $movie->workerID = null;
      $movie->workerName = null;
    } else {
      $movie->workerID = $worker->id;
      $movie->workerName = $worker->getAttribute('firstname') . ' ' . $worker->getAttribute('surname');
    }

    if($emergencyWorker == null) {
      $movie->emergencyWorkerID = null;
      $movie->emergencyWorkerName = null;
    } else {
      $movie->emergencyWorkerID = $emergencyWorker->id;
      $movie->emergencyWorkerName = $emergencyWorker->getAttribute('firstname') . ' ' . $emergencyWorker->getAttribute('surname');
    }

    $movie->view_movies = [
      'href' => 'api/v1/cinema/movie',
      'method' => 'GET'
    ];

    $response = [
      'msg' => 'Movie information',
      'movie' => $movie
    ];
    return response()->json($response);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  int $id
   * @return \Illuminate\Http\Response
   * @throws \Illuminate\Validation\ValidationException
   */
  public function update(Request $request, $id)
  {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'date' => 'required|date',
      'trailerLink' => 'required|max:190|min:1',
      'posterLink' => 'required|max:190|min:1',
      'bookedTickets' => 'integer',
      'movie_year_id' => 'required|integer'
    ]);

    $name = $request->input('name');
    $date = $request->input('date');
    $trailerLink = $request->input('trailerLink');
    $posterLink = $request->input('posterLink');
    $bookedTickets = $request->input('bookedTickets');
    $movie_year_id = $request->input('movie_year_id');

    $movie = Movie::find($id);

    if($movie == null) {
      return response()->json(['msg' => 'Movie does not exist'], 404);
    }

    if(MovieYear::find($movie_year_id) == null) {
      return response()->json(['msg' => 'Movie year does not exist'], 404);
    }

    $movie->name = $name;
    $movie->date = $date;
    $movie->trailerLink = $trailerLink;
    $movie->posterLink = $posterLink;
    $movie->bookedTickets = $bookedTickets;
    $movie->movie_year_id = $movie_year_id;

    if($movie->save()) {
      $movie->view_movie = [
        'href' => 'api/v1/cinema/movie/'.$movie->getAttribute('id'),
        'method' => 'GET'
      ];

      $response = [
        'msg' => 'Movie updated',
        'year' => $movie
      ];

      return response()->json($response, 201);
    }

    $response = [
      'msg' => 'An error occurred'
    ];

    return response()->json($response, 404);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param  int $id
   * @return \Illuminate\Http\Response
   */
  public function delete($id)
  {
    $movie = Movie::find($id);
    if($movie == null) {
      return response()->json(['msg' => 'Movie not found'], 404);
    }

    if(!$movie->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    $response = [
      'msg' => 'Movie deleted',
      'create' => [
        'href' => 'api/v1/cinema/movie',
        'method' => 'POST',
        'params' => 'name, date, trailerLink, posterLink, bookedTickets, movie_year_id'
      ]
    ];

    return response()->json($response);
  }

  public function getNotShownMovies(Request $request)
  {
    $allMovies = Movie::all();
    if($allMovies == null) {
      $response = [
        'msg' => 'List of not shown movies',
        'movies' => $allMovies
      ];

      return response()->json($response);
    }

    $user = $request->auth;

    $movies = null;

    foreach ($allMovies as $movie) {
      if((time()-(60*60*24)) < strtotime($movie->date. ' 20:00:00')) {
        $movies[] = $movie;
      }
    }

    foreach ($movies as $movie) {
      $workerID = $movie->worker_id;
      $emergencyWorkerID = $movie->emergency_worker_id;

      $worker = User::find($workerID);
      $emergencyWorker = User::find($emergencyWorkerID);

      if($worker == null) {
        $movie->workerID = null;
        $movie->workerName = null;
      } else {
        $movie->workerID = $worker->id;
        $movie->workerName = $worker->getAttribute('firstname') . ' ' . $worker->getAttribute('surname');
      }

      if($emergencyWorker == null) {
        $movie->emergencyWorkerID = null;
        $movie->emergencyWorkerName = null;
      } else {
        $movie->emergencyWorkerID = $emergencyWorker->id;
        $movie->emergencyWorkerName = $emergencyWorker->getAttribute('firstname') . ' ' . $emergencyWorker->getAttribute('surname');
      }

      $movieBookingForYourself = $user->moviesBookings()->where('movie_id', $movie->id)->first();

      if($movieBookingForYourself == null) {
        $movie->bookedTicketsForYourself = 0;
      } else {
        $movie->bookedTicketsForYourself = $movieBookingForYourself->amount;
      }

      $movie->view_movie = [
        'href' => 'api/v1/cinema/movie/'.$movie->getAttribute('id'),
        'method' => 'GET'
      ];
    }

    $response = [
      'msg' => 'List of not shown movies',
      'movies' => $movies
    ];

    return response()->json($response);
  }
}