<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\PerformanceBadge\Instrument;
use App\Models\PerformanceBadge\PerformanceBadge;
use App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserChange\IUserChangeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PerformanceBadgeController extends Controller {
  protected IUserRepository $userRepository;
  protected IUserChangeRepository $userChangeRepository;

  public function __construct(IUserRepository $userRepository, IUserChangeRepository $userChangeRepository) {
    $this->userRepository = $userRepository;
    $this->userChangeRepository = $userChangeRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @return JsonResponse
   */
  public function getAll() {
    $performanceBadges = PerformanceBadge::orderBy('name')
      ->get();

    $response = [
      'msg' => 'List of all performance badges',
      'performanceBadges' => $performanceBadges, ];

    return response()->json($response);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, ['name' => 'required|max:190|min:1']);

    $name = $request->input('name');

    if (PerformanceBadge::where('name', $name)
      ->first() != null) {
      return response()->json([
        'msg' => 'Performance badge already exist',
        'error' => 'performance_badge_already_exists', ], 400);
    }

    $performanceBadge = new PerformanceBadge(['name' => $name]);
    if (! $performanceBadge->save()) {
      return response()->json(['msg' => 'An error occurred during performance badge saving..'], 500);
    }

    $response = [
      'msg' => 'Performance badge successful created',
      'performanceBadge' => $performanceBadge, ];

    return response()->json($response, 201);
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(int $id) {
    $performanceBadge = PerformanceBadge::find($id);

    if ($performanceBadge == null) {
      return response()->json(['msg' => 'Performance badge not found'], 404);
    }

    $response = [
      'msg' => 'Performance badge information',
      'performanceBadge' => $performanceBadge, ];

    return response()->json($response);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, int $id) {
    $this->validate($request, ['name' => 'required|max:190|min:1',]);

    $performanceBadge = PerformanceBadge::find($id);
    if ($performanceBadge == null) {
      return response()->json([
        'msg' => 'Performance badge not found',
        'error_code' => 'performanceBadge_not_found', ], 404);
    }

    $name = $request->input('name');

    $performanceBadge->name = $name;

    if (! $performanceBadge->save()) {
      return response()->json(['msg' => 'An error occurred during performance badge saving..'], 500);
    }

    $response = [
      'msg' => 'Performance badge updated',
      'performanceBadge' => $performanceBadge, ];

    return response()->json($response, 200);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return JsonResponse
   */
  public function delete(int $id) {
    $performanceBadge = PerformanceBadge::find($id);
    if ($performanceBadge == null) {
      return response()->json(['msg' => 'Performance badge not found'], 404);
    }

    if (! $performanceBadge->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    return response()->json(['msg' => 'Performance badge deleted'], 201);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addPerformanceBadgeForUserWithInstrument(Request $request) {
    $this->validate($request, [
      'user_id' => 'required|numeric',
      'performanceBadge_id' => 'required|numeric',
      'instrument_id' => 'required|numeric',
      'date' => 'date',
      'grade' => 'max:190',
      'note' => 'max:190', ]);

    $userId = $request->input('user_id');
    if ($this->userRepository->getUserById($userId) == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $performanceBadgeId = $request->input('performanceBadge_id');
    $performanceBadge = PerformanceBadge::find($performanceBadgeId);
    if ($performanceBadge == null) {
      return response()->json(['msg' => 'Performance badge not found'], 404);
    }

    $instrumentId = $request->input('instrument_id');
    $instrument = Instrument::find($instrumentId);
    if ($instrument == null) {
      return response()->json(['msg' => 'Instrument not found'], 404);
    }

    $grade = $request->input('grade');
    $userHasPerformanceBadgeWithInstrument = new UserHavePerformanceBadgeWithInstrument([
      'performance_badge_id' => $performanceBadgeId,
      'instrument_id' => $instrumentId,
      'user_id' => $userId,
      'date' => $request->input('date'),
      'grade' => $grade,
      'note' => $request->input('note'), ]);

    if (! $userHasPerformanceBadgeWithInstrument->save()) {
      return response()->json(['msg' => 'Could not save UsersHavePerformanceBadgeWithInstrument'], 500);
    }

    $this->userChangeRepository->createUserChange(
      'performance badge',
      $userId,
      $request->auth->id,
      $performanceBadge->name . '; ' . $instrument->name . '; ' . $grade,
      null
    );

    return response()->json(['msg' => 'Successful added performance badge with instrument to user'], 200);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function removePerformanceBadgeForUserWithInstrument(Request $request, int $id) {
    $userHasPerformanceBadgeWithInstrument = UserHavePerformanceBadgeWithInstrument::find($id);
    if ($userHasPerformanceBadgeWithInstrument == null) {
      return response()->json(['msg' => 'User performance badge with instrument not found'], 202);
    }

    $this->userChangeRepository->createUserChange(
      'performance badge',
      $userHasPerformanceBadgeWithInstrument->user_id,
      $request->auth->id,
      null,
      $userHasPerformanceBadgeWithInstrument->performanceBadge()->name . '; ' . $userHasPerformanceBadgeWithInstrument->instrument()->name . '; ' . $userHasPerformanceBadgeWithInstrument->grade
    );
    if (! $userHasPerformanceBadgeWithInstrument->delete()) {
      return response()->json(['msg' => 'Could not delete user performance badge with instrument'], 500);
    }

    return response()->json(['msg' => 'Successfully deleted user performance badge with instrument'], 200);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function performanceBadgesForUser(int $id): JsonResponse {
    $user = $this->userRepository->getUserById($id);
    if ($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    return response()->json([
      'msg' => 'List of all performance badges for user ' . $user->id,
      'performanceBadges' => $user->getPerformanceBadges(), ], 200);
  }
}
