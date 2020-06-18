<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Models\PerformanceBadge\Badge;
use App\Models\PerformanceBadge\Instrument;
use App\Models\PerformanceBadge\PerformanceBadge;
use App\Models\PerformanceBadge\UserHasBadge;
use App\Models\PerformanceBadge\UserHavePerformanceBadgeWithInstrument;
use App\Models\User\User;
use App\Repositories\User\User\IUserRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use stdClass;

class BadgeController extends Controller
{

  protected $userRepository = null;

  public function __construct(IUserRepository $userRepository) {
    $this->userRepository = $userRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function getAll(Request $request) {
    $badges = Badge::orderBy('name')->get();

    Logging::info('getAllBadges', 'Get all badges! User id - ' . $request->auth->id);
    return response()->json(['msg' => 'List of all badges', 'badges' => $badges]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, ['description' => 'required|max:190|min:1', 'afterYears' => 'integer']);

    $description = $request->input('description');
    $afterYears = $request->input('date');

    $badge = new Badge(['description' => $description, 'afterYears' => $afterYears]);
    if (!$badge->save()) {
      Logging::error('createBadge', 'Could not create badge! User id - ' . $request->auth->id);
      return response()->json(['msg' => 'An error occurred during performance badge saving..'], 500);
    }

    Logging::info('createBadge', 'Badge created id -' . $badge->id . ' User id - ' . $request->auth->id);
    return response()->json(['msg' => 'Badge successful created', 'badge' => $badge], 201);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function delete(Request $request, int $id) {
    $badge = Badge::find($id);
    if ($badge == null) {
      return response()->json(['msg' => 'Badge not found'], 404);
    }

    if (!$badge->delete()) {
      Logging::error('deleteBadge', 'Could not delete badge! User id - ' . $request->auth->id);
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    Logging::info('deleteBadge', 'Badge delete! User id - ' . $request->auth->id);
    return response()->json(['msg' => 'Badge deleted'], 201);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addUserBadge(Request $request) {
    $this->validate($request, ['description' => 'required|max:190|min:1', 'getDate' => 'date', 'reason' => 'max:190']);

    $description = $request->input('description');
    $date = $request->input('date');
    $reason = $request->input('reason');

    $userHasBadge = new UserHasBadge(['description' => $description, 'getDate' => $date, 'reason' => $reason]);
    if (!$userHasBadge->save()) {
      Logging::error('createUserBadge', 'Could not create user badge! User id - ' . $request->auth->id);
      return response()->json(['msg' => 'An error occurred during user badge saving..'], 500);
    }

    Logging::info('createUserBadge', 'UserBadge created id -' . $userHasBadge->id . ' User id - ' . $request->auth->id);
    return response()->json(['msg' => 'UserBadge successful created', 'userHasBadge' => $userHasBadge], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function removeUserBadge(Request $request, int $id) {
    $badge = UserHasBadge::find($id);
    if ($badge == null) {
      return response()->json(['msg' => 'UserBadge not found'], 404);
    }

    if (!$badge->delete()) {
      Logging::error('deleteUserBadge', 'Could not delete user badge! User id - ' . $request->auth->id);
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    Logging::info('deleteBadge', 'Badge delete! User id - ' . $request->auth->id);
    return response()->json(['msg' => 'Badge deleted'], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function userBadgesForUser(Request $request, int $id) {
    $user = $this->userRepository->getUserById($id);
    if($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $userBadges = UserHasBadge::where('user_id', $user->id)->get();

    Logging::info('userBadgesForUser', 'UserBadges requested! User id - ' . $request->auth->id);
    return response()->json(['msg' => 'List of all user badges for user ' . $user->id, 'badges' => $userBadges], 200);
  }
}
