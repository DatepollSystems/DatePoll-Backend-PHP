<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Permissions;
use App\Repositories\User\DeletedUser\IDeletedUserRepository;
use App\Repositories\User\User\IUserRepository;
use Illuminate\Http\JsonResponse;

class DeletedUsersController extends Controller {

  public function __construct(protected IDeletedUserRepository $deletedUserRepository, protected IUserRepository $userRepository) {
  }

  /**
   * Display a listing of the resource.
   *
   * @return JsonResponse
   */
  public function getDeletedUsers(): JsonResponse {
    return response()->json([
      'msg' => 'List of deleted users',
      'users' => $this->deletedUserRepository->getDeletedUsers(),]);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function delete(AuthenticatedRequest $request, int $id): JsonResponse {
    if (! ($request->auth->hasPermission(Permissions::$ROOT_ADMINISTRATION) || $request->auth->hasPermission(Permissions::$MANAGEMENT_EXTRA_USER_DELETE))) {
      return response()->json([
        'msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$MANAGEMENT_EXTRA_USER_DELETE,],], 403);
    }

    $user = $this->userRepository->getUserById($id);
    if ($user == null) {
      return response()->json(['msg' => 'User not found'], 404);
    }

    if ($request->auth->id == $id) {
      return response()->json(['msg' => 'Can not delete yourself'], 400);
    }

    if (! $this->deletedUserRepository->deleteUser($user)) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    return response()->json(['msg' => 'User deleted'], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function deleteAllDeletedUsers(AuthenticatedRequest $request): JsonResponse {
    if (! ($request->auth->hasPermission(Permissions::$ROOT_ADMINISTRATION) || $request->auth->hasPermission(Permissions::$MANAGEMENT_EXTRA_USER_DELETE))) {
      return response()->json([
        'msg' => 'Permission denied',
        'error_code' => 'permissions_denied',
        'needed_permissions' => [
          Permissions::$ROOT_ADMINISTRATION,
          Permissions::$MANAGEMENT_EXTRA_USER_DELETE,],], 403);
    }

    Logging::info('deleteDeletedUsers', 'Deleting all deleted users... User id - ' . $request->auth->id);
    $this->deletedUserRepository->deleteAllDeletedUsers();
    Logging::info('deleteDeletedUsers', 'Deleted all deleted users! User id - ' . $request->auth->id);

    return response()->json(['msg' => 'Deleted users successfully deleted'], 200);
  }
}
