<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventListController extends Controller
{

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getOpenEvents(Request $request) {
    $user = $request->auth;

    $events = $user->getOpenEvents();

    return response()->json([
      'msg' => 'List of events',
      'events' => $events], 200);
  }

}