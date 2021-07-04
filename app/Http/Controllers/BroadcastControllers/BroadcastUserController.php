<?php

namespace App\Http\Controllers\BroadcastControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use App\Utils\StringHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class BroadcastUserController extends Controller {
  protected IBroadcastRepository $broadcastRepository;

  public function __construct(IBroadcastRepository $broadcastRepository) {
    $this->broadcastRepository = $broadcastRepository;
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $page
   * @param int $pageSize
   * @return JsonResponse
   */
  public function getAll(AuthenticatedRequest $request, int $page = 0, int $pageSize = 15): JsonResponse {
    return response()->json([
      'msg' => 'List broadcasts',
      'page' => $page,
      'page_size' => $pageSize,
      'broadcasts' => $this->broadcastRepository->getBroadcastsForUserOrderedByDate($request->auth->id, $pageSize, $page), ]);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function searchBroadcast(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, [
      'search' => 'required|string', ]);

    $search = StringHelper::trim($request->input('search'));

    return response()->json(['msg' => 'Searched broadcasts', 'search' => $search,
      'broadcasts' => $this->broadcastRepository->searchBroadcasts($search), ]);
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

    if (! $this->broadcastRepository->isUserAllowedToViewBroadcast($request->auth->id, $broadcast)) {
      return response()->json(['msg' => 'You are not allowed to view this broadcast', 'error_code' => 'insufficient_permission'], 403);
    }

    return response()->json([
      'msg' => 'Information for broadcast',
      'broadcast' => $broadcast->toArrayWithBodyHTML(),
    ]);
  }
}
