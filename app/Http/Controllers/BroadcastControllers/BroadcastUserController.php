<?php

namespace App\Http\Controllers\BroadcastControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use Illuminate\Http\JsonResponse;

class BroadcastUserController extends Controller {
  protected IBroadcastRepository $broadcastRepository;

  public function __construct(IBroadcastRepository $broadcastRepository) {
    $this->broadcastRepository = $broadcastRepository;
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getAll(AuthenticatedRequest $request): JsonResponse {
    return response()->json([
      'msg' => 'List of all broadcasts',
      'broadcasts' => $this->broadcastRepository->getBroadcastsForUserByIdOrderedByDate($request->auth->id), ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(AuthenticatedRequest $request, int $id): JsonResponse {
    $broadcast = $this->broadcastRepository->getBroadcastById($id);
    if ($broadcast == null) {
      return response()->json(['msg' => 'Broadcast not found', 'error_code' => 'not_found'], 404);
    }

    if (! $this->broadcastRepository->isUserByIdAllowedToViewBroadcastById($request->auth->id, $id)) {
      return response()->json(['msg' => 'You are not allowed to view this broadcast', 'error_code' => 'insufficient_permission'], 403);
    }

    return response()->json([
      'msg' => 'Information for broadcast',
      'broadcast' => $broadcast->toArrayWithBodyHTML(),
    ]);
  }
}
