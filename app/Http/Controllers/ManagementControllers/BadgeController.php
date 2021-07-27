<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Models\PerformanceBadge\Badge;
use App\Models\PerformanceBadge\UserHasBadge;
use App\Repositories\User\User\IUserRepository;
use App\Repositories\User\UserChange\IUserChangeRepository;
use App\Utils\ArrayHelper;
use App\Utils\DateHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use JetBrains\PhpStorm\ArrayShape;
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
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getAll(AuthenticatedRequest $request): JsonResponse {
    $badges = Badge::orderBy('description')
      ->get();

    Logging::info('getAllBadges', 'Get all badges! User id - ' . $request->auth->id);

    return response()->json([
      'msg' => 'List of all badges',
      'badges' => $badges,]);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, [
      'description' => 'required|max:190|min:1',
      'after_years' => 'required|integer',]);

    $description = $request->input('description');
    $afterYears = $request->input('after_years');

    $badge = new Badge([
      'description' => $description,
      'afterYears' => $afterYears,]);
    if (! $badge->save()) {
      Logging::error('createBadge', 'Could not create badge! User id - ' . $request->auth->id);

      return response()->json(['msg' => 'An error occurred during performance badge saving..'], 500);
    }

    Logging::info('createBadge', 'Badge created id -' . $badge->id . ' User id - ' . $request->auth->id);

    return response()->json([
      'msg' => 'Badge successful created',
      'badge' => $badge,], 201);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function delete(AuthenticatedRequest $request, int $id): JsonResponse {
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
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addUserBadge(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, [
      'description' => 'required|max:190|min:1',
      'get_date' => 'date',
      'reason' => 'max:190',
      'user_id' => 'required|integer',]);

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
      'user_id' => $userId,]);
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
      'userBadge' => $this->getUserBadgeReturnable($userHasBadge),], 201);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function removeUserBadge(AuthenticatedRequest $request, int $id): JsonResponse {
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
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function userBadgesForUser(AuthenticatedRequest $request, int $id): JsonResponse {
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
      'userBadges' => $toReturn,], 200);
  }

  /**
   * @param UserHasBadge|Model $badge
   * @return array
   */
  #[ArrayShape(['id' => 'int', 'description' => 'string', 'get_date' => 'string', 'reason' => 'string',
    'created_at' => 'string', 'updated_at' => 'string', 'user_id' => 'int', ])]
  private function getUserBadgeReturnable(UserHasBadge | Model $badge): array {
    return ['id' => $badge->id, 'description' => $badge->description, 'get_date' => $badge->getDate,
      'reason' => $badge->reason, 'created_at' => $badge->created_at, 'updated_at' => $badge->updated_at,
      'user_id' => $badge->user_id, ];
  }

  /**
   * @param int|null $year
   * @return JsonResponse
   */
  public function getYearBadges(int $year = null): JsonResponse {
    if ($year == null) {
      $year = DateHelper::getYearOfDate();
    }

    $cacheKey = 'year.badges.' . $year;

    if (Cache::has($cacheKey)) {
      return response()->json(['msg' => 'Current year badges',
        'users' => Cache::get($cacheKey),]);
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

      if (ArrayHelper::getSize($badges) > 0) {
        $userT->current_year_badges = $badges;
        $currentYearBadgesForUser[] = $userT;
      }
    }

    Logging::info('getYearBadges', 'Getting year badges for: ' . $year . '! Saving into cache: ' . $cacheKey . '!');
    //TTL 48 hours
    Cache::put($cacheKey, $currentYearBadgesForUser, 60 * 60 * 48);

    return response()->json([
      'msg' => 'Current year badges',
      'users' => $currentYearBadgesForUser,]);
  }
}
