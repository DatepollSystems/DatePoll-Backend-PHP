<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Permissions;
use App\Repositories\User\UserChange\IUserChangeRepository;
use Exception;
use Illuminate\Http\JsonResponse;

class UserChangesController extends Controller {

  public function __construct(protected IUserChangeRepository $userChangeRepository) {
  }

  /**
   * Returns all changes to users
   *
   * @return JsonResponse
   */
  public function getAllUserChanges(): JsonResponse {
    return response()->json(['msg' => 'User changes', 'user_changes' => $this->userChangeRepository->getAllUserChangesOrderedByDate()], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function deleteUserChange(AuthenticatedRequest $request, int $id): JsonResponse {
    if (! ($request->auth->hasPermission(Permissions::$ROOT_ADMINISTRATION) || $request->auth->hasPermission(Permissions::$MANAGEMENT_EXTRA_USER_DELETE))) {
      return response()->json([
        'msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$MANAGEMENT_EXTRA_USER_DELETE,],], 403);
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
