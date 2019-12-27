<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Events\EventDecision;
use App\Models\Events\EventUserVotedForDecision;
use App\Models\User\User;
use App\Repositories\Event\Event\IEventRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventVoteController extends Controller
{

  protected $eventRepository = null;

  public function __construct(IEventRepository $eventRepository) {
    $this->eventRepository = $eventRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function vote(Request $request) {
    $this->validate($request, [
      'decision_id' => 'required|integer',
      'event_id' => 'required|integer',
      'additional_information' => 'nullable|string|max:128|min:1']);

    $eventId = $request->input('event_id');
    if (Event::find($eventId) == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $eventDecisions = EventDecision::where('event_id', $eventId)
                                   ->where('id', $request->input('decision_id'))
                                   ->first();
    if ($eventDecisions == null) {
      return response()->json(['msg' => 'Decision not found for this event'], 404);
    }

    $user = $request->auth;

    // Check if user is in a group for this event
    $allowedToVote = false;
    foreach ($this->eventRepository->getOpenEventsForUser($user) as $openEvent) {
      if ($eventId === $openEvent->id) {
        if ($openEvent->already_voted) {
          return response()->json(['msg' => 'User already voted for event'], 400);
        }

        $allowedToVote = true;
        break;
      }
    }

    if (!$allowedToVote) {
      return response()->json(['msg' => 'You are not allowed to vote for this event'], 400);
    }

    $eventUserVotedForDecision = new EventUserVotedForDecision([
      'additionalInformation' => $request->input('additional_information'),
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

    $eventUserVotedForDecision = EventUserVotedForDecision::where('event_id', $id)
                                                          ->where('user_id', $user->id)
                                                          ->first();

    if ($eventUserVotedForDecision != null) {
      if (!$eventUserVotedForDecision->delete()) {
        return response()->json(['msg' => 'Could not delete decision'], 500);
      }

      return response()->json(['msg' => 'Decision for event removed successfully'], 200);
    }

    return response()->json(['msg' => 'There is no decision for this event to remove'], 404);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function voteForUsers(Request $request, $id) {
    $this->validate($request, [
      'decision_id' => 'required|integer',
      'additional_information' => 'nullable|string|max:128|min:1',
      'user_ids' => 'array']);

    $event = Event::find($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $eventDecision = EventDecision::where('event_id', $id)
                                  ->where('id', $request->input('decision_id'))
                                  ->first();
    if ($eventDecision == null) {
      return response()->json(['msg' => 'Decision not found for this event'], 404);
    }

    $userIds = $request->input('user_ids');
    foreach ((array)$userIds as $userId) {
      if (!User::exists($userId)) {
        return response()->json([
          'msg' => 'User not found',
          'user_id' => $userId], 404);
      }

      $eventUserVotedForDecision = EventUserVotedForDecision::where('event_id', $id)
                                                            ->where('user_id', $userId)
                                                            ->first();
      if ($eventUserVotedForDecision != null) {
        if (!$eventUserVotedForDecision->delete()) {
          return response()->json(['msg' => 'Could not delete old decisions'], 500);
        }
      }

      $eventUserVotedForDecision = new EventUserVotedForDecision([
        'additionalInformation' => $request->input('additional_information'),
        'event_id' => $id,
        'decision_id' => $eventDecision->id,
        'user_id' => $userId]);

      if (!$eventUserVotedForDecision->save()) {
        return response()->json(['msg' => 'Could not save eventUserVotedForDecision'], 500);
      }
    }

    return response()->json(['msg' => 'Successfully applied all votes'], 200);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function cancelVotingForUsers(Request $request, $id) {
    $this->validate($request, ['user_ids' => 'array']);

    $event = Event::find($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $userIds = $request->input('user_ids');
    foreach ((array)$userIds as $userId) {
      if (!User::exists($userId)) {
        return response()->json([
          'msg' => 'User not found',
          'user_id' => $userId], 404);
      }

      $eventUserVotedForDecision = EventUserVotedForDecision::where('event_id', $id)
                                                            ->where('user_id', $userId)
                                                            ->first();
      if ($eventUserVotedForDecision != null) {
        if (!$eventUserVotedForDecision->delete()) {
          return response()->json(['msg' => 'Could not delete old decisions'], 500);
        }
      }
    }

    return response()->json(['msg' => 'Decisions for event removed successfully'], 200);
  }
}