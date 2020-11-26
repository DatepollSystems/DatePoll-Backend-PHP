<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Repositories\Event\Event\IEventRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventListController extends Controller {
  protected IEventRepository $eventRepository;

  public function __construct(IEventRepository $eventRepository) {
    $this->eventRepository = $eventRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getOpenEvents(Request $request) {
    $user = $request->auth;

    $events = $this->eventRepository->getOpenEventsForUser($user);

    return response()->json([
      'msg' => 'List of events',
      'events' => $events, ], 200);
  }
}
