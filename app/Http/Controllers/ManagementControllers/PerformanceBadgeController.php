<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\PerformanceBadge\Instrument;
use App\Models\PerformanceBadge\PerformanceBadge;
use App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument;
use App\Repositories\User\User\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use stdClass;

class PerformanceBadgeController extends Controller
{

  protected $userRepository = null;

  public function __construct(IUserRepository $userRepository) {
    $this->userRepository = $userRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @return JsonResponse
   */
  public function getAll() {
    $performanceBadges = PerformanceBadge::orderBy('name')->get();

    $response = ['msg' => 'List of all performance badges', 'performanceBadges' => $performanceBadges];

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

    if (PerformanceBadge::where('name', $name)->first() != null) {
      return response()->json(['msg' => 'Performance badge already exist', 'error' => 'performance_badge_already_exists'], 400);
    }

    $performanceBadge = new PerformanceBadge(['name' => $name]);
    if (!$performanceBadge->save()) {
      return response()->json(['msg' => 'An error occurred during performance badge saving..'], 500);
    }

    $response = ['msg' => 'Performance badge successful created', 'performanceBadge' => $performanceBadge];

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

    $response = ['msg' => 'Performance badge information', 'performanceBadge' => $performanceBadge];
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
      return response()->json(['msg' => 'Performance badge not found', 'error_code' => 'performanceBadge_not_found'], 404);
    }

    $name = $request->input('name');

    $performanceBadge->name = $name;

    if (!$performanceBadge->save()) {
      return response()->json(['msg' => 'An error occurred during performance badge saving..'], 500);
    }

    $response = ['msg' => 'Performance badge updated', 'performanceBadge' => $performanceBadge];

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

    if (!$performanceBadge->delete()) {
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
      'note' => 'max:190']);

    $userId = $request->input('user_id');
    if($this->userRepository->getUserById($userId) == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $performanceBadgeId = $request->input('performanceBadge_id');
    if(PerformanceBadge::find($performanceBadgeId) == null) {
      return response()->json(['msg' => 'Performance badge not found'], 404);
    }

    $instrumentId = $request->input('instrument_id');
    if(Instrument::find($instrumentId) == null) {
      return response()->json(['msg' => 'Instrument not found'], 404);
    }

    $userHasPerformanceBadgeWithInstrument = new UserHavePerformanceBadgeWithInstrument([
      'performance_badge_id' => $performanceBadgeId,
      'instrument_id' => $instrumentId,
      'user_id' => $userId,
      'date' => $request->input('date'),
      'grade' => $request->input('grade'),
      'note' => $request->input('note')]);

    if(!$userHasPerformanceBadgeWithInstrument->save()) {
      return response()->json(['msg' => 'Could not save UsersHavePerformanceBadgeWithInstrument'], 500);
    }

    return response()->json(['msg' => 'Successful added performance badge with instrument to user'], 200);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function removePerformanceBadgeForUserWithInstrument(int $id) {
    $userHasPerformanceBadgeWithInstrument = UserHavePerformanceBadgeWithInstrument::find($id);
    if($userHasPerformanceBadgeWithInstrument == null) {
      return response()->json(['msg' => 'User performance badge with instrument not found'], 202);
    }

    if(!$userHasPerformanceBadgeWithInstrument->delete()) {
      return response()->json(['msg' => 'Could not delete user performance badge with instrument'], 500);
    }

    return response()->json(['msg' => 'Successfully deleted user performance badge with instrument'], 200);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function performanceBadgesForUser(int $id) {
    $user = $this->userRepository->getUserById($id);
    if($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $performanceBadgesToReturn = [];

    $userHasPerformanceBadgesWithInstruments = $user->performanceBadges();
    foreach ($userHasPerformanceBadgesWithInstruments as $performanceBadgeWithInstrument) {
      $performanceBadgeToReturn = new stdClass();
      $performanceBadgeToReturn->id = $performanceBadgeWithInstrument->id;
      $performanceBadgeToReturn->performanceBadge_id = $performanceBadgeWithInstrument->performance_badge_id;
      $performanceBadgeToReturn->instrument_id = $performanceBadgeWithInstrument->instrument_id;
      $performanceBadgeToReturn->grade = $performanceBadgeWithInstrument->grade;
      $performanceBadgeToReturn->note = $performanceBadgeWithInstrument->note;
      if($performanceBadgeWithInstrument->date != '1970-01-01') {
        $performanceBadgeToReturn->date = $performanceBadgeWithInstrument->date;
      } else {
        $performanceBadgeToReturn->date = null;
      }
      $performanceBadgeToReturn->performanceBadge_name = $performanceBadgeWithInstrument->performanceBadge()->name;
      $performanceBadgeToReturn->instrument_name = $performanceBadgeWithInstrument->instrument()->name;

      $performanceBadgesToReturn[] = $performanceBadgeToReturn;
    }

    return response()->json(['msg' => 'List of all performance badges for user ' . $user->id, 'performanceBadges' => $performanceBadgesToReturn], 200);
  }
}
