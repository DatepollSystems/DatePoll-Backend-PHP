<?php

namespace App\Http\Controllers\BroadcastControllers;

use App\Http\Controllers\Controller;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BroadcastUserController extends Controller
{

  protected $broadcastRepository = null;

  public function __construct(IBroadcastRepository $broadcastRepository) {
    $this->broadcastRepository = $broadcastRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getAll(Request $request): JsonResponse {
    $broadcasts = $this->broadcastRepository->getBroadcastsForUserByIdOrderedByDate($request->auth->id);
    $toReturnBroadcasts = array();
    foreach ($broadcasts as $broadcast) {
      $toReturnBroadcasts[] = $this->broadcastRepository->getBroadcastReturnable($broadcast);
    }

    return response()->json([
      'msg' => 'List of all broadcasts',
      'broadcasts' => $toReturnBroadcasts]);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(Request $request, int $id): JsonResponse {
    $broadcast = $this->broadcastRepository->getBroadcastById($id);
    if ($broadcast == null) {
      return response()->json(['msg' => 'Broadcast not found', 'error_code' => 'not_found'], 404);
    }

    if (!$this->broadcastRepository->isUserByIdAllowedToViewBroadcastById($request->auth->id, $id)) {
      return response()->json(['msg' => 'You are not allowed to view this broadcast', 'error_code' => 'insufficient_permission'], 403);
    }

    return response()->json([
      'msg' => 'Information for broadcast',
      'broadcast' => $this->broadcastRepository->getBroadcastUserReturnable($broadcast)
    ]);
  }
}
