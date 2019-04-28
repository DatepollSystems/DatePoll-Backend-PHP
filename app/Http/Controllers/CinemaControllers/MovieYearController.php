<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Models\Cinema\MovieYear;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class MovieYearController extends Controller
{
  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function index() {
    $years = MovieYear::orderBy('year')->get();
    foreach ($years as $year) {
      $year->view_year = ['href' => 'api/v1/cinema/year/' . $year->getAttribute('id'), 'method' => 'GET'];
    }

    $response = ['msg' => 'List of all years', 'years' => $years];

    return response()->json($response);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @return Response
   * @throws ValidationException
   */
  public function store(Request $request) {
    $this->validate($request, ['year' => 'required|integer']);

    $yearValue = $request->input('year');

    $year = new MovieYear(['year' => $yearValue]);

    if ($year->save()) {
      $year->view_year = ['href' => 'api/v1/cinema/year/' . $year->getAttribute('id'), 'method' => 'GET'];

      $response = ['msg' => 'Year created', 'year' => $year];

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
  public function show($id) {
    $year = MovieYear::find($id);
    if ($year == null) {
      return response()->json(['msg' => 'Movie year not found'], 404);
    }

    $year->view_years = ['href' => 'api/v1/cinema/year', 'method' => 'GET'];

    $response = ['msg' => 'Year information', 'year' => $year];
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
    $this->validate($request, ['year' => 'required|integer']);

    $yearValue = $request->input('year');

    $year = MovieYear::find($id);
    if ($year == null) {
      return response()->json(['msg' => 'Movie year not found'], 404);
    }
    $year->year = $yearValue;
    if ($year->save()) {
      $year->view_year = ['href' => 'api/v1/cinema/year/' . $year->getAttribute('id'), 'method' => 'GET'];

      $response = ['msg' => 'Year updated', 'year' => $year];

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
  public function destroy($id) {
    $year = MovieYear::find($id);
    if ($year == null) {
      return response()->json(['msg' => 'Movie year not found'], 404);
    }

    if (!$year->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 404);
    }

    $response = ['msg' => 'Year deleted', 'create' => ['href' => 'api/v1/cinema/year', 'method' => 'POST', 'params' => 'year']];

    return response()->json($response);
  }
}
