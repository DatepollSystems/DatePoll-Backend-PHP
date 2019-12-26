<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Repositories\Event\Event\IEventRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{

  protected $eventRepository = null;

  public function __construct(IEventRepository $eventRepository) {
    $this->eventRepository = $eventRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $events = $this->eventRepository->getAllEventsOrderedByDate();

    $toReturnEvents = array();
    foreach ($events as $event) {
      $eventToReturn = $event->getReturnable();

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

    $toReturnEvent = $event->getReturnable();
    $toReturnEvent->resultGroups = $event->getResults($user);
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
      'dates' => 'array|required']);

    $name = $request->input('name');
    $forEveryone = $request->input('forEveryone');
    $description = $request->input('description');
    $decisions = $request->input('decisions');
    $dates = $request->input('dates');

    $event = $this->eventRepository->createOrUpdateEvent($name, $forEveryone, $description, $decisions, $dates);

    $returnable = $event->getReturnable();
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
      'startDate' => 'required|date',
      'endDate' => 'required|date',
      'forEveryone' => 'required|boolean',
      'description' => 'string|nullable',
      'location' => 'string|nullable|max:190',
      'decisions' => 'array']);

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

    $returnable = $event->getReturnable();
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