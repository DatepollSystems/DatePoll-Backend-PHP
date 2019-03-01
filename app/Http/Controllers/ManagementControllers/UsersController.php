<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use DB;
use Illuminate\Http\Request;

class UsersController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return \Illuminate\Http\Response
   */
  public function getAll()
  {
    $toReturnUsers = array();

    $users = User::all();
    foreach ($users as $user) {

      $toReturnUser = new \stdClass();

      $toReturnUser->id = $user->id;
      $toReturnUser->email = $user->email;
      $toReturnUser->title = $user->title;
      $toReturnUser->firstname = $user->firstname;
      $toReturnUser->surname = $user->surname;
      $toReturnUser->birthday = $user->birthday;
      $toReturnUser->join_date = $user->join_date;
      $toReturnUser->streetname = $user->streetname;
      $toReturnUser->streetnumber = $user->streetnumber;
      $toReturnUser->zipcode = $user->zipcode;
      $toReturnUser->location = $user->location;
      $toReturnUser->force_password_change = $user->force_password_change;
      $toReturnUser->activated = $user->activated;
      $toReturnUser->activity = $user->activity;

      $user->view_user = [
        'href' => 'api/v1/management/users/'.$user->id,
        'method' => 'GET'
      ];

      $userPermissions = DB::table('user_permissions')->where('user_id', '=', $user->id)->get();
      $permissions = array();
      foreach ($userPermissions as $permission) {
        $permissions[] = $permission->permission;
      }

      $toReturnUser->permissions = $permissions;

      $userTelephoneNumbers = DB::table('user_telephone_numbers')->where('user_id', '=', $user->id)->get();
      $telephoneNumbers = array();
      foreach ($userTelephoneNumbers as $telephoneNumber) {
        $telephoneNumbers[] = [
          'number' => $telephoneNumber->number,
          'label' => $telephoneNumber->label
        ];
      }

      $toReturnUser->telephoneNumbers = $telephoneNumbers;

      $toReturnUsers[] = $toReturnUser;
    }

    $response = [
      'msg' => 'List of all users',
      'users' => $toReturnUsers
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
      'name' => 'required|max:255|min:1',
      'date' => 'required|date',
      'trailerLink' => 'required|max:255|min:1',
      'posterLink' => 'required|max:255|min:1',
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

    $worker = $movie->worker();
    $emergencyWorker = $movie->emergencyWorker();

    if($worker == null) {
      $movie->workerName = null;
    } else {
      $movie->workerName = $worker->getAttribute('firstname') . ' ' . $worker->getAttribute('surname');
    }

    if($emergencyWorker == null) {
      $movie->emergencyWorkerName = null;
    } else {
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
      'name' => 'required|max:255|min:1',
      'date' => 'required|date',
      'trailerLink' => 'required|max:255|min:1',
      'posterLink' => 'required|max:255|min:1',
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
      return response()->json(['msg' => 'Deletion failed'], 404);
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

  public function getNotShownMovies()
  {
    $allMovies = Movie::all();
    if($allMovies == null) {
      $response = [
        'msg' => 'List of not shown movies',
        'movies' => $allMovies
      ];

      return response()->json($response);
    }

    $movies = null;

    $i = 0;
    foreach ($allMovies as $movie) {
      if((time()-(60*60*24)) < strtotime($movie->date. ' 22:00:00')) {
        $movies[$i] = $movie;
        $i++;
      }
    }

    foreach ($movies as $movie) {
      $worker = $movie->worker();
      $emergencyWorker = $movie->emergencyWorker();

      if($worker == null) {
        $movie->workerName = null;
      } else {
        $movie->workerName = $worker->getAttribute('firstname') . ' ' . $worker->getAttribute('surname');
      }

      if($emergencyWorker == null) {
        $movie->emergencyWorkerName = null;
      } else {
        $movie->emergencyWorkerName = $emergencyWorker->getAttribute('firstname') . ' ' . $emergencyWorker->getAttribute('surname');
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