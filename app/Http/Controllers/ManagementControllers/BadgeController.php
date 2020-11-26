<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Models\PerformanceBadge\Badge;
use App\Models\PerformanceBadge\UserHasBadge;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserChange\IUserChangeRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use stdClass;

class BadgeController extends Controller {
  protected IUserRepository $userRepository;
  protected IUserChangeRepository $userChangeRepository;

  public function __construct(IUserRepository $userRepository, IUserChangeRepository $userChangeRepository) {
    $this->userRepository = $userRepository;
    $this->userChangeRepository = $userChangeRepository;
  }

  /**
   * Display a listing of the resource.
   *
   * @param Request $request
   * @return JsonResponse
   */
  public function getAll(Request $request) {
    $badges = Badge::orderBy('description')
      ->get();

    Logging::info('getAllBadges', 'Get all badges! User id - ' . $request->auth->id);

    return response()->json([
      'msg' => 'List of all badges',
      'badges' => $badges, ]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'description' => 'required|max:190|min:1',
      'after_years' => 'required|integer', ]);

    $description = $request->input('description');
    $afterYears = $request->input('after_years');

    $badge = new Badge([
      'description' => $description,
      'afterYears' => $afterYears, ]);
    if (! $badge->save()) {
      Logging::error('createBadge', 'Could not create badge! User id - ' . $request->auth->id);

      return response()->json(['msg' => 'An error occurred during performance badge saving..'], 500);
    }

    Logging::info('createBadge', 'Badge created id -' . $badge->id . ' User id - ' . $request->auth->id);

    return response()->json([
      'msg' => 'Badge successful created',
      'badge' => $badge, ], 201);
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

    if (! $badge->delete()) {
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
    $this->validate($request, [
      'description' => 'required|max:190|min:1',
      'get_date' => 'date',
      'reason' => 'max:190',
      'user_id' => 'required|integer', ]);

    $userId = $request->input('user_id');
    if ($this->userRepository->getUserById($userId) == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    $description = $request->input('description');
    $getDate = $request->input('get_date');
    $reason = $request->input('reason');

    $userHasBadge = new UserHasBadge([
      'description' => $description,
      'getDate' => $getDate,
      'reason' => $reason,
      'user_id' => $userId, ]);
    if (! $userHasBadge->save()) {
      Logging::error('createUserBadge', 'Could not create user badge! User id - ' . $request->auth->id);

      return response()->json(['msg' => 'An error occurred during user badge saving..'], 500);
    }

    $this->userChangeRepository->createUserChange(
      'badge',
      $userId,
      $request->auth->id,
      $description . '; ' . $getDate . '; ' . $reason,
      null
    );

    Logging::info('createUserBadge', 'UserBadge created id -' . $userHasBadge->id . ' User id - ' . $request->auth->id);

    return response()->json([
      'msg' => 'UserBadge successful created',
      'userBadge' => $this->getUserBadgeReturnable($userHasBadge), ], 201);
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

    $this->userChangeRepository->createUserChange(
      'badge',
      $badge->user_id,
      $request->auth->id,
      null,
      $badge->description . '; ' . $badge->getDate . '; ' . $badge->reason
    );

    if (! $badge->delete()) {
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
    if ($user == null) {
      Logging::warning('userBadgesForUser', 'User not found with id - ' . $id . '! User id - ' . $request->auth->id);

      return response()->json(['msg' => 'User not found'], 404);
    }

    $userBadges = UserHasBadge::where('user_id', $user->id)
      ->orderBy('getDate')
      ->get();

    $toReturn = [];
    foreach ($userBadges as $userBadge) {
      $toReturn[] = $this->getUserBadgeReturnable($userBadge);
    }

    Logging::info('userBadgesForUser', 'UserBadges requested! User id - ' . $request->auth->id);

    return response()->json([
      'msg' => 'List of all user badges for user ' . $user->id,
      'userBadges' => $toReturn, ], 200);
  }

  /**
   * @param UserHasBadge $userHasBadge
   * @return stdClass
   */
  private function getUserBadgeReturnable(UserHasBadge $userHasBadge) {
    $returnable = new stdClass();

    $returnable->id = $userHasBadge->id;
    $returnable->description = $userHasBadge->description;
    $returnable->get_date = $userHasBadge->getDate;
    $returnable->reason = $userHasBadge->reason;
    $returnable->created_at = $userHasBadge->created_at;
    $returnable->updated_at = $userHasBadge->updated_at;
    $returnable->user_id = $userHasBadge->user_id;

    return $returnable;
  }

  /**
   * @param int|null $year
   * @return JsonResponse
   */
  public function getYearBadges(int $year = null): JsonResponse {
    if ($year == null) {
      $year = date('Y');
    }

    $cacheKey = 'year.badges.' . $year;

    if (Cache::has($cacheKey) && false) {
      return response()->json(['msg' => 'Current year badges',
        'users' => Cache::get($cacheKey), ]);
    }

    $currentYearBadgesForUser = [];

    foreach ($this->userRepository->getAllUsersOrderedBySurname() as $user) {
      $userT = new stdClass();
      $userT->id = $user->id;
      $userT->firstname = $user->firstname;
      $userT->surname = $user->surname;
      $userT->join_date = $user->join_date;

      $badges = [];
      $joinDate = strtotime($user->join_date);
      foreach (Badge::all() as $badge) {
        $dateWithAfterBadgeYears = strtotime('+' . $badge->afterYears . ' years', $joinDate);

        if ($year == date('Y', $dateWithAfterBadgeYears)) {
          if (UserHasBadge::where('user_id', '=', $user->id)
            ->where('description', '=', $badge->description)
            ->first() == null) {
            $badges[] = $badge;
          }
        }
      }

      if (sizeof($badges) > 0) {
        $userT->current_year_badges = $badges;
        $currentYearBadgesForUser[] = $userT;
      }
    }

    Logging::info('getYearBadges', 'Getting year badges for: ' . $year . '! Saving into cache: ' . $cacheKey . '!');
    //TTL 48 hours
    Cache::put($cacheKey, $currentYearBadgesForUser, 60 * 60 * 48);

    return response()->json([
      'msg' => 'Current year badges',
      'users' => $currentYearBadgesForUser, ]);
  }
}
