<?php

namespace App\Http\Controllers\ManagementControllers;

use App\Http\Controllers\Controller;
use App\Models\PerformanceBadge\Instrument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class InstrumentController extends Controller {

  /**
   * Display a listing of the resource.
   *
   * @return JsonResponse
   */
  public function getAll() {
    $instruments = Instrument::orderBy('name')->get();

    $response = ['msg' => 'List of all instruments', 'instruments' => $instruments];

    return response()->json($response);
  }

  /**
   * Store a newly created resource in storage.
   *
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, ['name' => 'required|max:190|min:1']);

    $name = $request->input('name');

    if (Instrument::where('name', $name)->first() != null) {
      return response()->json(['msg' => 'Instrument already exist', 'error' => 'instrument_already_exists'], 400);
    }

    $instrument = new Instrument(['name' => $name]);
    if (! $instrument->save()) {
      return response()->json(['msg' => 'An error occurred during instrument saving..'], 500);
    }

    $response = ['msg' => 'Instrument successful created', 'instruments' => $instrument];

    return response()->json($response, 201);
  }

  /**
   * Display the specified resource.
   *
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(int $id) {
    $instrument = Instrument::find($id);

    if ($instrument == null) {
      return response()->json(['msg' => 'Instrument not found'], 404);
    }

    $response = ['msg' => 'Instrument information', 'instrument' => $instrument];

    return response()->json($response);
  }

  /**
   * Update the specified resource in storage.
   *
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, int $id) {
    $this->validate($request, ['name' => 'required|max:190|min:1',]);

    $instrument = Instrument::find($id);
    if ($instrument == null) {
      return response()->json(['msg' => 'Instrument not found', 'error_code' => 'Instrument_not_found'], 404);
    }

    $name = $request->input('name');

    $instrument->name = $name;

    if (! $instrument->save()) {
      return response()->json(['msg' => 'An error occurred during instrument saving..'], 500);
    }

    $response = ['msg' => 'Instrument updated', 'instrument' => $instrument];

    return response()->json($response, 200);
  }

  /**
   * Remove the specified resource from storage.
   *
   * @param int $id
   * @return JsonResponse
   */
  public function delete(int $id) {
    $instrument = Instrument::find($id);
    if ($instrument == null) {
      return response()->json(['msg' => 'Instrument not found'], 404);
    }

    if (! $instrument->delete()) {
      return response()->json(['msg' => 'Deletion failed'], 500);
    }

    $response = ['msg' => 'Instrument deleted', 'create' => ['href' => 'api/v1/management/instruments', 'method' => 'POST', 'params' => 'name']];

    return response()->json($response);
  }
}
