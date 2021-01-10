<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Repositories\Event\EventStandardDecision\IEventStandardDecisionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StandardDecisionController extends Controller {
  /**
   * @param IEventStandardDecisionRepository $eventStandardDecisionRepository
   */
  public function __construct(protected IEventStandardDecisionRepository $eventStandardDecisionRepository) {
  }

  /**
   * @return JsonResponse
   */
  public function getAll(): JsonResponse {
    return response()->json([
      'msg' => 'List of all standard decisions',
      'standardDecisions' => $this->eventStandardDecisionRepository->getAllStandardDecisionsOrderedByName(), ]);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(int $id): JsonResponse {
    $standardDecision = $this->eventStandardDecisionRepository->getStandardDecisionById($id);

    if ($standardDecision == null) {
      return response()->json(['msg' => 'Standard decision not found'], 404);
    }

    return response()->json([
      'msg' => 'Standard decision information',
      'standardDecision' => $standardDecision, ]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request): JsonResponse {
    $this->validate($request, ['decision' => 'required|max:190|min:1', 'show_in_calendar' => 'required|boolean', 'color' => 'required|min:1|max:7']);

    $decision = $request->input('decision');
    $showInCalendar = $request->input('show_in_calendar');
    $color = $request->input('color');

    $decisionObject = $this->eventStandardDecisionRepository->createStandardDecision($decision, $showInCalendar, $color);
    if ($decisionObject == null) {
      return response()->json(['msg' => 'An error occurred during standard decision saving...'], 500);
    }

    return response()->json([
      'msg' => 'Successful created standard decision',
      'standardDecision' => $decisionObject, ], 201);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function delete(int $id): JsonResponse {
    if (! $this->eventStandardDecisionRepository->deleteStandardDecision($id)) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    return response()->json(['msg' => 'Standard decision deleted'], 200);
  }
}
