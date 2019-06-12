<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Events\EventUserVotedForDecision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use stdClass;

class EventListController extends Controller
{

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getOpenEvents(Request $request) {
    $user = $request->auth;

    $events = array();
    $allEvents = Event::orderBy('startDate')->get();
    foreach ($allEvents as $event) {
      if ((time() - (60 * 60 * 24)) < strtotime($event->startDate)) {

        $in = false;

        foreach ($event->eventsForGroups() as $eventForGroup) {
          foreach ($eventForGroup->group()->usersMemberOfGroups() as $userMemberOfGroup) {
            if ($userMemberOfGroup->user_id == $user->id) {
              $in = true;
              break;
            }
          }
        }

        if (!$in) {
          foreach ($event->eventsForSubgroups() as $eventForSubgroup) {
            foreach ($eventForSubgroup->subgroup()->usersMemberOfSubgroups() as $userMemberOfSubgroup) {
              if ($userMemberOfSubgroup->user_id == $user->id) {
                $in = true;
                break;
              }
            }
          }
        }

        if ($in) {
          $eventUserVotedFor = EventUserVotedForDecision::where('event_id', $event->id)->where('user_id', $user->id)->first();
          $alreadyVoted = ($eventUserVotedFor != null);

          $eventToReturn = new stdClass();
          $eventToReturn = $event->getReturnable();
          $eventToReturn->alreadyVoted = $alreadyVoted;
          $events[] = $eventToReturn;
        }
      }
    }

    return response()->json([
      'msg' => 'List of events',
      'events' => $events], 200);
  }

}