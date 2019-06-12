<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
        'href' => 'api/v1/event/administration/event/' . $event->id,
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
      'href' => 'api/v1/event/administration/event',
      'method' => 'GET'];

    return response()->json([
      'msg' => 'Event information',
      'event' => $event]);
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
      'decisions' => 'array|required']);

    $name = $request->input('name');
    $startDate = $request->input('startDate');
    $endDate = $request->input('endDate');
    $forEveryone = $request->input('forEveryone');
    $description = $request->input('description');

    $event = new Event([
      'name' => $name,
      'startDate' => $startDate,
      'endDate' => $endDate,
      'forEveryone' => $forEveryone,
      'description' => $description]);

    if (!$event->save()) {
      return response()->json(['msg' => 'An error occurred during event saving...'], 500);
    }

    $decisions = $request->input('decisions');
    foreach ((array)$decisions as $decision) {
      $eventDecision = new EventDecision([
        'event_id' => $event->id,
        'decision' => $decision]);

      if (!$eventDecision->save()) {
        return response()->json(['msg' => 'Could not save event decisions'], 500);
      }
    }

    $returnable = $event->getReturnable();
    $returnable->view_event = [
      'href' => 'api/v1/event/administration/event/' . $event->id,
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

    if (!$event->save()) {
      return response()->json(['msg' => 'An error occurred during event saving...'], 500);
    }

    DB::table('events_decisions')->where('event_id', '=', $event->id)->delete();

    $decisions = $request->input('decisions');
    foreach ((array)$decisions as $decision) {
      $eventDecision = new EventDecision([
        'event_id' => $event->id,
        'decision' => $decision]);

      if (!$eventDecision->save()) {
        return response()->json(['msg' => 'Could not save event decisions'], 500);
      }
    }

    $returnable = $event->getReturnable();
    $returnable->view_event = [
      'href' => 'api/v1/event/administration/event/' . $event->id,
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