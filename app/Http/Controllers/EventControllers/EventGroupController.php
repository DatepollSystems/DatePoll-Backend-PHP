<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Models\Events\Event;
use App\Models\Events\EventForGroup;
use App\Models\Events\EventForSubgroup;
use App\Models\Groups\Group;
use App\Models\Subgroups\Subgroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class EventGroupController extends Controller
{

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addGroupToEvent(Request $request) {
    $this->validate($request, [
      'group_id' => 'required|integer',
      'event_id' => 'required|integer']);

    $groupId = $request->input('group_id');
    if (Group::find($groupId) == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $eventId = $request->input('event_id');
    if (Event::find($eventId) == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $eventForGroup = EventForGroup::where('group_id', $groupId)->where('event_id', $eventId)->first();
    if ($eventForGroup != null) {
      return response()->json(['msg' => 'Group is already added to event'], 201);
    }

    $eventForGroup = new EventForGroup([
      'group_id' => $groupId,
      'event_id' => $eventId]);

    if (!$eventForGroup->save()) {
      return response()->json(['msg' => 'Could not add group to event'], 500);
    }

    return response()->json(['msg' => 'Group successfully added to event'], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function addSubgroupToEvent(Request $request) {
    $this->validate($request, [
      'subgroup_id' => 'required|integer',
      'event_id' => 'required|integer']);

    $subgroupId = $request->input('subgroup_id');
    $subgroup = Subgroup::find($subgroupId);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $eventId = $request->input('event_id');
    if (Event::find($eventId) == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $eventForSubgroup = EventForSubgroup::where('subgroup_id', $subgroupId)->where('event_id', $eventId)->first();
    if ($eventForSubgroup != null) {
      return response()->json(['msg' => 'Subgroup is already added to event'], 201);
    }

    $eventForSubgroup = new EventForSubgroup([
      'event_id' => $eventId,
      'subgroup_id' => $subgroupId]);

    if (!$eventForSubgroup->save()) {
      return response()->json(['msg' => 'Could not add subgroup to event'], 500);
    }

    return response()->json(['msg' => 'Successfully added subgroup to event'], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function removeGroupFromEvent(Request $request) {
    $this->validate($request, [
      'group_id' => 'required|integer',
      'event_id' => 'required|integer']);

    $groupId = $request->input('group_id');
    if (Group::find($groupId) == null) {
      return response()->json(['msg' => 'Group not found'], 404);
    }

    $eventId = $request->input('event_id');
    if (Event::find($eventId) == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $eventForGroup = EventForGroup::where('group_id', $groupId)->where('event_id', $eventId)->first();
    if ($eventForGroup == null) {
      return response()->json(['msg' => 'Group is not added to event'], 201);
    }

    if (!$eventForGroup->delete()) {
      return response()->json(['msg' => 'Could not remove group from event'], 500);
    }

    return response()->json(['msg' => 'Successfully remove group from event'], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function removeSubgroupFromEvent(Request $request) {
    $this->validate($request, [
      'subgroup_id' => 'required|integer',
      'event_id' => 'required|integer']);

    $subgroupId = $request->input('subgroup_id');
    $subgroup = Subgroup::find($subgroupId);
    if ($subgroup == null) {
      return response()->json(['msg' => 'Subgroup not found'], 404);
    }

    $eventId = $request->input('event_id');
    if (Event::find($eventId) == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $eventForSubgroup = EventForSubgroup::where('subgroup_id', $subgroupId)->where('event_id', $eventId)->first();
    if ($eventForSubgroup == null) {
      return response()->json(['msg' => 'Subgroup is not added to event'], 201);
    }

    if (!$eventForSubgroup->delete()) {
      return response()->json(['msg' => 'Could not remove subgroup from event'], 500);
    }

    return response()->json(['msg' => 'Successfully remove subgroup from event'], 200);
  }

  /**
   * @param $id
   * @return JsonResponse
   */
  public function groupJoined($id) {
    $event = Event::find($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $groupsToReturn = array();
    $eventForGroups = EventForGroup::where('event_id', $id)->get();
    foreach ($eventForGroups as $eventForGroup) {
      $group = $eventForGroup->group();

      $group->view_group = [
        'href' => 'api/v1/management/groups/' . $group->id,
        'method' => 'GET'];

      $groupsToReturn[] = $group;
    }

    return response()->json([
      'msg' => 'List of joined groups',
      'groups' => $groupsToReturn], 200);
  }

  /**
   * @param $id
   * @return JsonResponse
   */
  public function groupFree($id) {
    $event = Event::find($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $allGroups = Group::all();
    $groupsToReturn = array();
    $eventForGroups = EventForGroup::where('event_id', $id)->get();
    foreach ($allGroups as $group) {
      $isInGroup = false;
      foreach ($eventForGroups as $eventForGroup) {
        if ($eventForGroup->group()->id == $group->id) {
          $isInGroup = true;
          break;
        }
      }

      if (!$isInGroup) {
        $group->view_group = [
          'href' => 'api/v1/management/groups/' . $group->id,
          'method' => 'GET'];

        $groupsToReturn[] = $group;
      }
    }

    return response()->json([
      'msg' => 'List of free groups',
      'groups' => $groupsToReturn], 200);
  }

  /**
   * @param $id
   * @return JsonResponse
   */
  public function subgroupJoined($id) {
    $event = Event::find($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $subgroupsToReturn = array();

    $eventForSubgroups = EventForSubgroup::where('event_id', $id)->get();
    foreach ($eventForSubgroups as $eventForSubgroup) {
      $subgroup = $eventForSubgroup->subgroup();

      $subgroup->group_name = $subgroup->group()->name;

      $subgroup->view_subgroup = [
        'href' => 'api/v1/management/subgroups/' . $subgroup->id,
        'method' => 'GET'];

      $subgroupsToReturn[] = $subgroup;
    }

    return response()->json([
      'msg' => 'List of joined subgroups',
      'subgroups' => $subgroupsToReturn], 200);
  }

  /**
   * @param $id
   * @return JsonResponse
   */
  public function subgroupFree($id) {
    $event = Event::find($id);
    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $allSubgroups = Subgroup::all();
    $subgroupsToReturn = array();
    $eventForSubgroups = EventForSubgroup::where('event_id', $id)->get();
    foreach ($allSubgroups as $subgroup) {
      $isInSubgroup = false;
      foreach ($eventForSubgroups as $eventForSubgroup) {
        if ($eventForSubgroup->subgroup()->id == $subgroup->id) {
          $isInSubgroup = true;
          break;
        }
      }

      if (!$isInSubgroup) {
        $subgroup->group_name = $subgroup->group()->name;

        $subgroup->view_subgroup = [
          'href' => 'api/v1/management/subgroups/' . $subgroup->id,
          'method' => 'GET'];

        $subgroupsToReturn[] = $subgroup;
      }
    }

    return response()->json([
      'msg' => 'List of free subgroups',
      'subgroups' => $subgroupsToReturn], 200);
  }
}