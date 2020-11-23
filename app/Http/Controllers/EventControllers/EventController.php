<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Permissions;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Event\EventDate\IEventDateRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;

class EventController extends Controller {
  private static string $YEARS_CACHE_KEY = 'events.years';

  protected IEventRepository $eventRepository;
  protected IEventDateRepository $eventDateRepository;

  public function __construct(IEventRepository $eventRepository, IEventDateRepository $eventDateRepository) {
    $this->eventRepository = $eventRepository;
    $this->eventDateRepository = $eventDateRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getYearsOfEvents() {
    if (Cache::has(self::$YEARS_CACHE_KEY)) {
      $years = Cache::get(self::$YEARS_CACHE_KEY);
    } else {
      $years = $this->eventRepository->getYearsOfEvents();
      // Time to live 3 hours
      Cache::put(self::$YEARS_CACHE_KEY, $years, 3 * 60 * 60);
    }

    return response()->json(['msg' => 'List of all years', 'years' => $years]);
  }

  /**
   * @param int|null $year
   * @return JsonResponse
   */
  public function getEventsOrderedByDate(int $year = null) {
    $events = $this->eventRepository->getEventsOrderedByDate($year);

    $toReturnEvents = [];
    foreach ($events as $event) {
      $toReturnEvents[] = $this->eventRepository->getReturnable($event);
    }

    return response()->json([
      'msg' => 'List of all events of this year',
      'events' => $toReturnEvents, ]);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(Request $request, int $id) {
    $event = $this->eventRepository->getEventById($id);

    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $user = $request->auth;
    $anonymous = ! ($user->hasPermission(Permissions::$ROOT_ADMINISTRATION) ||
      $user->hasPermission(Permissions::$EVENTS_ADMINISTRATION) ||
      $user->hasPermission(Permissions::$EVENTS_VIEW_DETAILS));
    $anonymousString = true === (bool)$anonymous ? 'true' : 'false';

    $cacheKey = 'events.results.anonymous.' . $anonymousString . '.' . $id;
    if (Cache::has($cacheKey)) {
      Logging::info('getSingleEvent', 'Receiving ' . $cacheKey . ' from cache');

      return response()->json([
        'msg' => 'Event information',
        'event' => Cache::get($cacheKey), ]);
    }
    Logging::info('getSingleEvent', 'Generating ' . $cacheKey . ' and saving to cache');

    $toReturnEvent = $this->eventRepository->getReturnable($event);
    $toReturnEvent->resultGroups = $this->eventRepository->getResultsForEvent($event, $anonymous);

    // Time to live 5 minutes
    Cache::put($cacheKey, $toReturnEvent, 60 * 5);

    return response()->json([
      'msg' => 'Event information',
      'event' => $toReturnEvent, ]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   * @throws Exception
   */
  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'forEveryone' => 'required|boolean',
      'description' => 'string|nullable',
      'decisions' => 'array|required',
      'decisions.*.decision' => 'string|min:1|max:190',
      'decisions.*.show_in_calendar' => 'required|boolean',
      'decisions.*.color' => 'required|min:1|max:7',
      'dates' => 'array|required',
      'dates.*.x' => 'nullable|numeric',
      'dates.*.y' => 'nullable|numeric',
      'dates.*.date' => 'date|nullable',
      'dates.*.location' => 'string|nullable|max:190',
      'dates.*.description' => 'string|nullable|max:255', ]);

    $name = $request->input('name');
    $forEveryone = $request->input('forEveryone');
    $description = $request->input('description');
    $decisions = $request->input('decisions');
    $dates = $request->input('dates');

    $event = $this->eventRepository->createOrUpdateEvent($name, $forEveryone, $description, $decisions, $dates);

    $returnable = $this->eventRepository->getReturnable($event);
    $returnable->view_event = [
      'href' => 'api/v1/avent/administration/avent/' . $event->id,
      'method' => 'GET', ];

    Cache::forget(self::$YEARS_CACHE_KEY);

    return response()->json([
      'msg' => 'Successful created event',
      'event' => $returnable, ], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   * @throws Exception
   */
  public function update(Request $request, int $id) {
    $this->validate($request, [
      'name' => 'required|max:190|min:1',
      'forEveryone' => 'required|boolean',
      'description' => 'string|nullable',
      'decisions' => 'array|required',
      'decisions.*.id' => 'required|integer',
      'decisions.*.decision' => 'string|min:1|max:190',
      'decisions.*.show_in_calendar' => 'required|boolean',
      'decisions.*.color' => 'required|min:1|max:7',
      'dates' => 'array|required',
      'dates.*.id' => 'required|integer',
      'dates.*.x' => 'nullable|numeric',
      'dates.*.y' => 'nullable|numeric',
      'dates.*.date' => 'date|nullable',
      'dates.*.location' => 'string|nullable|max:190',
      'dates.*.description' => 'string|nullable|max:255', ]);

    $event = $this->eventRepository->getEventById($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $name = $request->input('name');
    $forEveryone = $request->input('forEveryone');
    $description = $request->input('description');
    $decisions = $request->input('decisions');
    $dates = $request->input('dates');

    $event = $this->eventRepository->createOrUpdateEvent($name, $forEveryone, $description, $decisions, $dates, $event);

    $returnable = $this->eventRepository->getReturnable($event);
    $returnable->view_event = [
      'href' => 'api/v1/avent/administration/avent/' . $event->id,
      'method' => 'GET', ];

    return response()->json([
      'msg' => 'Successful updated event',
      'event' => $returnable, ], 200);
  }

  /**
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(int $id) {
    $event = $this->eventRepository->getEventById($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    if (! $this->eventRepository->deleteEvent($event)) {
      return response()->json(['msg' => 'Could not delete event'], 500);
    }

    return response()->json(['msg' => 'Successfully deleted event'], 200);
  }
}
