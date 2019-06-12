<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use App\Models\Events\EventUserVotedForDecision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventVoteController extends Controller
{

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function vote(Request $request) {
    $this->validate($request, [
      'decision' => 'required|min:1|max:190',
      'event_id' => 'required|integer']);

    $eventId = $request->input('event_id');
    if (Event::find($eventId) == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $eventDecisions = EventDecision::where('event_id', $eventId)->where('decision', $request->input('decision'))->first();
    if ($eventDecisions == null) {
      return response()->json(['msg' => 'Decision not found for this event'], 404);
    }

    $user = $request->auth;

    $eventUserVotedForDecision = EventUserVotedForDecision::where('event_id', $eventId)->where('user_id', $user->id)->first();

    if ($eventUserVotedForDecision != null) {
      return response()->json(['msg' => 'User already voted for event'], 400);
    }

    $eventUserVotedForDecision = new EventUserVotedForDecision([
      'event_id' => $eventId,
      'decision_id' => $eventDecisions->id,
      'user_id' => $user->id]);

    if (!$eventUserVotedForDecision->save()) {
      return response()->json(['msg' => 'Could not save user voting...'], 500);
    }

    return response()->json([
      'msg' => 'Voting saved',
      'eventUserVotedForDecision' => $eventUserVotedForDecision], 200);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   */
  public function removeVoting(Request $request, $id) {
    if (Event::find($id) == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $user = $request->auth;

    $eventUserVotedForDecision = EventUserVotedForDecision::where('event_id', $id)->where('user_id', $user->id)->first();

    if ($eventUserVotedForDecision != null) {
      if (!$eventUserVotedForDecision->delete()) {
        return response()->json(['msg' => 'Could not delete decision'], 500);
      }

      return response()->json(['msg' => 'Decision for event removed'], 200);
    }

    return response()->json(['msg' => 'There is no decision for this event to remove'], 404);
  }

}