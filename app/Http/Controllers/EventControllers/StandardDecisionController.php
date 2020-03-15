<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Models\Events\EventStandardDecision;
use App\Repositories\Event\EventStandardDecision\IEventStandardDecisionRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StandardDecisionController extends Controller
{

  protected $eventStandardDecisionRepository = null;

  /**
   * StandardDecisionController constructor.
   * @param IEventStandardDecisionRepository $eventStandardDecisionRepository
   */
  public function __construct(IEventStandardDecisionRepository $eventStandardDecisionRepository) {
    $this->eventStandardDecisionRepository = $eventStandardDecisionRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $standardDecisions = $this->eventStandardDecisionRepository->getAllStandardDecisionsOrderedByName();

    $toReturn = array();
    foreach ($standardDecisions as $standardDecision) {
      $standardDecision->view_standard_decision = [
        'href' => 'api/v1/avent/administration/standardDecision/' . $standardDecision->id,
        'method' => 'GET'];

      $toReturn[] = $standardDecision;
    }

    return response()->json([
      'msg' => 'List of all standard decisions',
      'standardDecisions' => $toReturn]);
  }

  /**
   * @param $id
   * @return JsonResponse
   */
  public function getSingle($id) {
    $standardDecision = $this->eventStandardDecisionRepository->getStandardDecisionById($id);

    if ($standardDecision == null) {
      return response()->json(['msg' => 'Standard decision not found'], 404);
    }

    $standardDecision->view_standard_decisions = [
      'href' => 'api/v1/avent/administration/standardDecision',
      'method' => 'GET'];

    return response()->json([
      'msg' => 'Standard decision information',
      'standardDecision' => $standardDecision]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, ['decision' => 'required|max:190|min:1', 'show_in_calendar' => 'required|boolean', 'color' => 'required|min:1|max:7']);

    $decision = $request->input('decision');
    $showInCalendar = $request->input('show_in_calendar');
    $color = $request->input('color');

    $decisionObject = $this->eventStandardDecisionRepository->createStandardDecision($decision, $showInCalendar, $color);
    if ($decisionObject == null) {
      return response()->json(['msg' => 'An error occurred during standard decision saving...'], 500);
    }

    $decisionObject->view_standard_decision = [
      'href' => 'api/v1/avent/administration/standardDecision/' . $decisionObject->id,
      'method' => 'GET'];

    return response()->json([
      'msg' => 'Successful created standard decision',
      'standardDecision' => $decisionObject], 201);
  }

  /**
   * @param $id
   * @return JsonResponse
   */
  public function delete($id) {
    $standardDecision = $this->eventStandardDecisionRepository->getStandardDecisionById($id);

    if ($standardDecision == null) {
      return response()->json(['msg' => 'Standard decision not found'], 404);
    }

    if (!$this->eventStandardDecisionRepository->deleteStandardDecision($standardDecision->id)) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    return response()->json(['msg' => 'Standard decision deleted'], 200);
  }
}