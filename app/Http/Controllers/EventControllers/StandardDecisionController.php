<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Models\Events\EventStandardDecision;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StandardDecisionController extends Controller
{

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $standardDecisions = EventStandardDecision::orderBy('decision')->get();

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
    $standardDecision = EventStandardDecision::find($id);

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
    $this->validate($request, ['decision' => 'required|max:190|min:1', 'showInCalendar' => 'required|boolean']);

    $decision = $request->input('decision');
    $showInCalendar = $request->input('showInCalendar');

    $decisionObject = new EventStandardDecision(['decision' => $decision, 'showInCalendar' => $showInCalendar]);

    if (!$decisionObject->save()) {
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
   * @param Request $request
   * @param $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, $id) {
    $this->validate($request, ['decision' => 'required|max:190|min:1', 'showInCalendar' => 'required|boolean']);

    $standardDecision = EventStandardDecision::find($id);

    if ($standardDecision == null) {
      return response()->json(['msg' => 'Standard decision not found'], 404);
    }

    $standardDecision->decision = $request->input('decision');
    $standardDecision->showInCalendar = $request->input('showInCalendar');

    if (!$standardDecision->save()) {
      return response()->json(['msg' => 'An error occurred during standard decision saving...'], 500);
    }

    $standardDecision->view_standard_decision = [
      'href' => 'api/v1/avent/administration/standardDecision/' . $standardDecision->id,
      'method' => 'GET'];

    return response()->json([
      'msg' => 'Successful updated standard decision',
      'standardDecision' => $standardDecision], 200);
  }

  /**
   * @param $id
   * @return JsonResponse
   */
  public function delete($id) {
    $standardDecision = EventStandardDecision::find($id);

    if ($standardDecision == null) {
      return response()->json(['msg' => 'Standard decision not found'], 404);
    }

    if (!$standardDecision->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    return response()->json(['msg' => 'Standard decision deleted'], 200);
  }
}