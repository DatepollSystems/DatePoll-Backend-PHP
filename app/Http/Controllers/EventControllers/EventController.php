<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventController extends Controller
{

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $events = Event::orderBy('startDate')->get();

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
   * @param $id
   * @return JsonResponse
   */
  public function getSingle($id) {
    $event = Event::find($id);

    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $toReturnEvent = $event->getReturnable();
    $toReturnEvent->resultGroups = $event->getResults();
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
      'startDate' => 'required|date',
      'endDate' => 'required|date',
      'forEveryone' => 'required|boolean',
      'description' => 'string|nullable',
      'location' => 'string|nullable|max:190',
      'decisions' => 'array|required']);

    $name = $request->input('name');
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');
    $forEveryone = $request->input('forEveryone');
    $description = $request->input('description');
    $location = $request->input('location');

    $event = new Event([
      'name' => $name,
      'startDate' => $startDate,
      'endDate' => $endDate,
      'forEveryone' => $forEveryone,
      'description' => $description,
      'location' => $location]);

    if (!$event->save()) {
      return response()->json(['msg' => 'An error occurred during event saving...'], 500);
    }

    $decisions = $request->input('decisions');
    foreach ((array)$decisions as $decisionObject) {
      $decisionObject = (object) $decisionObject;

      $decision = $decisionObject->decision;
      $showInCalendar = $decisionObject->showInCalendar;

//      if (!is_string($decision) || !is_bool($showInCalendar)) {
//      $event->delete();
//        return response()->json(['msg' => 'Could not save event decisions... decision must be a string and showInCalendar must be a boolean'], 401);
//      }

      $eventDecision = new EventDecision([
        'event_id' => $event->id,
        'decision' => $decision,
        'showInCalendar' => $showInCalendar]);

      if (!$eventDecision->save()) {
        $event->delete();
        return response()->json(['msg' => 'Could not save event decisions'], 500);
      }
    }

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

    $event = Event::find($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $event->name = $request->input('name');
    $event->startDate = $request->input('startDate');
    $event->endDate = $request->input('endDate');
    $event->forEveryone = $request->input('forEveryone');
    $event->description = $request->input('description');
    $event->location = $request->input('location');;

    if (!$event->save()) {
      return response()->json(['msg' => 'An error occurred during event saving...'], 500);
    }

    //-------------------------------- Only delete changed decisions --------------------------------------
    $decisions = $request->input('decisions');
    $decisionsWhichHaveNotBeenDeleted = array();

    $oldDecisions = $event->eventsDecisions();
    foreach ($oldDecisions as $oldDecision) {
      $toDelete = true;

      foreach ((array)$decisions as $decision) {
        $decisionObject = (object) $decision;
        if ($oldDecision->id == $decisionObject->id) {
          $toDelete = false;
          $decisionsWhichHaveNotBeenDeleted[] = $oldDecision;
          break;
        }
      }

      if ($toDelete) {
        $decisionToDeleteObject = EventDecision::find($oldDecision->id);
        if (!$decisionToDeleteObject->delete()) {
          return response()->json(['msg' => 'Failed during decision clearing...'], 500);
        }
      }
    }

    foreach ((array)$decisions as $decision) {
      $decisionObject = (object) $decision;
      $toAdd = true;

      foreach ($decisionsWhichHaveNotBeenDeleted as $decisionWhichHaveNotBeenDeleted) {
        if ($decisionObject->id == $decisionWhichHaveNotBeenDeleted->id) {
          $toAdd = false;
          break;
        }
      }

      if ($toAdd) {
        $decisionString = $decisionObject->decision;
        $showInCalendar = $decisionObject->showInCalendar;

        $eventDecision = new EventDecision([
          'event_id' => $event->id,
          'decision' => $decisionString,
          'showInCalendar' => $showInCalendar]);

        if (!$eventDecision->save()) {
          $event->delete();
          return response()->json(['msg' => 'Could not save event decisions'], 500);
        }
      }
    }
    // ----------------------------------------------------------------------------------------------------

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
   */
  public function delete($id) {
    $event = Event::find($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    if (!$event->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    return response()->json(['msg' => 'Event deleted'], 200);
  }
}