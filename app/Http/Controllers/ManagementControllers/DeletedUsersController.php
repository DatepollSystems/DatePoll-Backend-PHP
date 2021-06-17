<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
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
   * @param int $id
   * @return JsonResponse
   */
  public function deleteSingleDeletedUsers(AuthenticatedRequest $request, int $id): JsonResponse {
    Logging::info('deleteDeletedUsers', 'Deleting single deleted users... User id - ' . $request->auth->id);
    $this->deletedUserRepository->deleteSingleDeletedUser($id);

    return response()->json(['msg' => 'Deleted user successfully deleted'], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function deleteAllDeletedUsers(AuthenticatedRequest $request): JsonResponse {
    Logging::info('deleteDeletedUsers', 'Deleting all deleted users... User id - ' . $request->auth->id);
    $this->deletedUserRepository->deleteAllDeletedUsers();
    Logging::info('deleteDeletedUsers', 'Deleted all deleted users! User id - ' . $request->auth->id);

    return response()->json(['msg' => 'Deleted users successfully deleted'], 200);
  }
}
