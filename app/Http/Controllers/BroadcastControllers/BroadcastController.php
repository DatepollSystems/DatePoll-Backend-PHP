<?php

namespace App\Http\Controllers\BroadcastControllers;

use App\Logging;
use App\Http\Controllers\Controller;
use App\Permissions;
use App\Repositories\Broadcast\Broadcast\IBroadcastRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
class BroadcastController extends Controller
{

  protected $broadcastRepository = null;

  public function __construct(IBroadcastRepository $broadcastRepository) {
    $this->broadcastRepository = $broadcastRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $broadcasts = $this->broadcastRepository->getAllBroadcastsOrderedByDate();
    $toReturnBroadcasts = array();
    foreach ($broadcasts as $broadcast) {
      $toReturnBroadcasts[] = $this->broadcastRepository->getBroadcastAdminReturnable($broadcast);
    }

    return response()->json([
      'msg' => 'List of all broadcasts',
      'broadcasts' => $toReturnBroadcasts]);
  }

  /**
   * @param $id
   * @return JsonResponse
   */
  public function getSentReceiptReturnable($id) {
    $broadcast = $this->broadcastRepository->getBroadcastById($id);
    if ($broadcast == null) {
      return response()->json(['msg' => 'Broadcast not found'], 404);
    }

    return response()->json([
      'msg' => 'Get broadcast with send receipts',
      'broadcast' => $this->broadcastRepository->getBroadcastSentReceiptReturnable($broadcast)]);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(Request $request, $id) {
    $event = $this->eventRepository->getEventById($id);

    if ($event == null) {
      return response()->json(['msg' => 'Event not found'], 404);
    }

    $user = $request->auth;

    $toReturnEvent = $this->eventRepository->getReturnable($event);
    $toReturnEvent->resultGroups = $this->eventRepository->getResultsForEvent($event, !($user->hasPermission(Permissions::$ROOT_ADMINISTRATION) || $user->hasPermission(Permissions::$EVENTS_ADMINISTRATION) || $user->hasPermission(Permissions::$EVENTS_VIEW_DETAILS)));

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
      'for_everyone' => 'required|boolean',
      'subject' => 'required|max:190|min:1',
      'bodyHTML' => 'required|string',
      'body' => 'required|string',
      'groups' => 'array',
      'groups.*' => 'required|integer',
      'subgroups' => 'array',
      'subgroups.*' => 'required|integer',]);

    $forEveryone = $request->input('for_everyone');
    $subject = $request->input('subject');
    $bodyHTML = $request->input('bodyHTML');
    $body = $request->input('body');

    $groups = (array)$request->input('groups');
    $subgroups = (array)$request->input('subgroups');

    $broadcast = $this->broadcastRepository->create($subject, $bodyHTML, $body, $request->auth->id, $groups, $subgroups, $forEveryone);

    if ($broadcast == null) {
      return response()->json(['msg' => 'Could not create broadcast'], 500);
    }

    return response()->json([
      'msg' => 'Successful created broadcast',
      'event' => $broadcast], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(Request $request, $id) {
    $broadcast = $this->broadcastRepository->getBroadcastById($id);
    if ($broadcast == null) {
      return response()->json(['msg' => 'Broadcast not found'], 404);
    }

    if (!$this->broadcastRepository->delete($broadcast)) {
      Logging::error('deleteBroadcast', 'Could not delete broadcast! User id - ' . $request->auth->id);
      return response()->json(['msg' => 'Could not delete broadcast'], 500);
    }

    Logging::info('deleteBroadcast', 'Deleted broadcast! User id - ' . $request->auth->id);
    return response()->json(['msg' => 'Successfully deleted broadcast'], 200);
  }
}