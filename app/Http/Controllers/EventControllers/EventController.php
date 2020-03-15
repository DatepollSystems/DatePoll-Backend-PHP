<?php

namespace App\Http\Controllers\EventControllers;

use App\Models\Events\Event;
use App\Http\Controllers\Controller;
use App\Permissions;
use App\Repositories\Event\Event\IEventRepository;
use App\Repositories\Event\EventDate\IEventDateRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{

  protected $eventRepository = null;
  protected $eventDateRepository = null;

  public function __construct(IEventRepository $eventRepository, IEventDateRepository $eventDateRepository) {
    $this->eventRepository = $eventRepository;
    $this->eventDateRepository = $eventDateRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $events = $this->eventRepository->getAllEventsOrderedByDate();

    $toReturnEvents = array();
    foreach ($events as $event) {
      $eventToReturn = $this->eventRepository->getReturnable($event);

      $eventToReturn->view_event = [
        'href' => 'api/v1/avent/administration/avent/' . $event->id,
        'method' => 'GET'];

      $toReturnEvents[] = $eventToReturn;
    }

    return response()->json([
      'msg' => 'List of all events',
      'events' => $toReturnEvents]);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   */
  public function getSingle(Request $request, $id) {
    $event = $this->eventRepository->getEventById($id);

    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $user = $request->auth;

    $toReturnEvent = $this->eventRepository->getReturnable($event);
    $toReturnEvent->resultGroups = $this->eventRepository->getResultsForEvent($event,
      !($user->hasPermission(Permissions::$ROOT_ADMINISTRATION) ||
        $user->hasPermission(Permissions::$EVENTS_ADMINISTRATION) ||
        $user->hasPermission(Permissions::$EVENTS_VIEW_DETAILS)));

    $toReturnEvent->view_events = [
      'href' => 'api/v1/avent/administration/avent',
      'method' => 'GET'];

    return response()->json([
      'msg' => 'Event information',
      'event' => $toReturnEvent]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
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
      'dates.*.description' => 'string|nullable|max:255']);

    $name = $request->input('name');
    $forEveryone = $request->input('forEveryone');
    $description = $request->input('description');
    $decisions = $request->input('decisions');
    $dates = $request->input('dates');

    $event = $this->eventRepository->createOrUpdateEvent($name, $forEveryone, $description, $decisions, $dates);

    $returnable = $this->eventRepository->getReturnable($event);
    $returnable->view_event = [
      'href' => 'api/v1/avent/administration/avent/' . $event->id,
      'method' => 'GET'];

    return response()->json([
      'msg' => 'Successful created event',
      'event' => $returnable], 201);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, $id) {
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
      'dates.*.description' => 'string|nullable|max:255']);

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
      'method' => 'GET'];

    return response()->json([
      'msg' => 'Successful updated event',
      'event' => $returnable], 200);
  }

  /**
   * @param $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete($id) {
    $event = $this->eventRepository->getEventById($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    if (!$this->eventRepository->deleteEvent($event)) {
      return response()->json(['msg' => 'Could not delete event'], 500);
    }

    return response()->json(['msg' => 'Successfully deleted event'], 200);
  }
}