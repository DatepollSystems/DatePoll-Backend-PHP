<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Repositories\Event\Event\IEventRepository;
use Illuminate\Http\JsonResponse;

class EventListController extends Controller {
  public function __construct(protected IEventRepository $eventRepository) {
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getOpenEvents(AuthenticatedRequest $request): JsonResponse {
    $user = $request->auth;

    $events = $this->eventRepository->getOpenEventsForUser($user);

    return response()->json([
      'msg' => 'List of events',
      'events' => $events, ], 200);
  }
}
