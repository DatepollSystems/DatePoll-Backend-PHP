<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\PerformanceBadge\PerformanceBadge;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;

class PerformanceBadgeController extends Controller
{

  /**
   * Display a listing of the resource.
   *
   * @return Response
   */
  public function getAll() {
    $performanceBadges = PerformanceBadge::all();
    foreach ($performanceBadges as $performanceBadge) {
      $performanceBadge->view_performanceBadge = ['href' => 'api/v1/management/performanceBadges/' . $performanceBadge->id, 'method' => 'GET'];
    }

    $response = ['msg' => 'List of all performance badges', 'performanceBadges' => $performanceBadges];

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
    $this->validate($request, ['name' => 'required|max:190|min:1']);

    $name = $request->input('name');

    if (PerformanceBadge::where('name', $name)->first() != null) {
      return response()->json(['msg' => 'Performance badge already exist', 'error' => 'performance_badge_already_exists'], 400);
    }

    $performanceBadge = new PerformanceBadge(['name' => $name]);
    if (!$performanceBadge->save()) {
      return response()->json(['msg' => 'An error occurred during performance badge saving..'], 500);
    }

    $performanceBadge->view_performanceBadge = ['href' => 'api/v1/management/performanceBadge/' . $performanceBadge->id, 'method' => 'GET'];

    $response = ['msg' => 'Performance badge successful created', 'performanceBadge' => $performanceBadge];

    return response()->json($response, 201);
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return Response
   */
  public function getSingle($id) {
    $performanceBadge = PerformanceBadge::find($id);

    if ($performanceBadge == null) {
      return response()->json(['msg' => 'Performance badge not found'], 404);
    }

    $performanceBadge->view_instruments = ['href' => 'api/v1/management/performanceBadges', 'method' => 'GET'];

    $response = ['msg' => 'Performance badge information', 'performanceBadge' => $performanceBadge];
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
    $this->validate($request, ['name' => 'required|max:190|min:1',]);

    $performanceBadge = PerformanceBadge::find($id);
    if ($performanceBadge == null) {
      return response()->json(['msg' => 'Performance badge not found', 'error_code' => 'performanceBadge_not_found'], 404);
    }

    $name = $request->input('name');

    $performanceBadge->name = $name;

    if (!$performanceBadge->save()) {
      return response()->json(['msg' => 'An error occurred during performance badge saving..'], 500);
    }

    $performanceBadge->view_performanceBadge = ['href' => 'api/v1/management/performanceBadges/' . $performanceBadge->id, 'method' => 'GET'];

    $response = ['msg' => 'Performance badge updated', 'performanceBadge' => $performanceBadge];

    return response()->json($response, 200);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return Response
   */
  public function delete($id) {
    $performanceBadge = PerformanceBadge::find($id);
    if ($performanceBadge == null) {
      return response()->json(['msg' => 'Performance badge not found'], 404);
    }

    if (!$performanceBadge->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    $response = ['msg' => 'Performance badge deleted', 'create' => ['href' => 'api/v1/management/performanceBadges', 'method' => 'POST', 'params' => 'name']];

    return response()->json($response);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function addPerformanceBadgeForUserWithInstrument(Request $request) {
    return response()->json(['msg' => 'Not implemented']);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function removePerformanceBadgeForUserWithInstrument(Request $request) {

    return response()->json(['msg' => 'Not implemented']);
  }
}
