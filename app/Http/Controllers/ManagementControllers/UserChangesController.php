<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Permissions;
use App\Repositories\User\UserChange\IUserChangeRepository;
use App\Utils\ResponseHelper;
use App\Utils\StringHelper;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class UserChangesController extends Controller {

  public function __construct(protected IUserChangeRepository $userChangeRepository) {
  }

  /**
   * Returns all changes to users
   *
   * @param int $page
   * @param int $pageSize
   * @return JsonResponse
   */
  public function getAllUserChanges(int $page = 0, int $pageSize = 15): JsonResponse {
    return response()->json(['msg' => 'User changes',
                             'user_changes' => $this->userChangeRepository->getAllUserChangesOrderedByDate($page,
                               $pageSize)], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function searchUserChanges(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, [
      'search' => 'required|string']);

    $search = $request->input('search');

    $ignoreEditor = false;
    if (StringHelper::contains($search, '!e')) {
      $ignoreEditor = true;
      $search = StringHelper::removeString($search, '!e');
    }
    $search = StringHelper::trim($search);

    return response()->json(['msg' => 'Searched user changes', 'search' => $search,
                             'user_changes' => $this->userChangeRepository->searchUserChange($search, $ignoreEditor)]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function deleteUserChange(AuthenticatedRequest $request, int $id): JsonResponse {
    if (! ($request->auth->hasPermission(Permissions::$ROOT_ADMINISTRATION) || $request->auth->hasPermission(Permissions::$MANAGEMENT_EXTRA_USER_DELETE))) {
      return ResponseHelper::getPermissionDeniedError(Permissions::$ROOT_ADMINISTRATION,
        Permissions::$MANAGEMENT_EXTRA_USER_DELETE);
    }

    $userChange = $this->userChangeRepository->getUserChangeById($id);
    if ($userChange == null) {
      return response()->json(['msg' => 'User change not found'], 404);
    }

    if (! $userChange->delete()) {
      return response()->json(['msg' => 'Could not delete user change'], 500);
    }

    return response()->json(['msg' => 'Successfully delete user change']);
  }
}
