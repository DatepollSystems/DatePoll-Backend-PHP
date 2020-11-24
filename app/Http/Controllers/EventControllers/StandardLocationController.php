<?php

namespace App\Http\Controllers\EventControllers;

use App\Http\Controllers\Controller;
use App\Repositories\Event\EventStandardLocation\IEventStandardLocationRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class StandardLocationController extends Controller {
  protected IEventStandardLocationRepository $eventStandardLocationRepository;

  public function __construct(IEventStandardLocationRepository $eventStandardLocationRepository) {
    $this->eventStandardLocationRepository = $eventStandardLocationRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $standardLocations = $this->eventStandardLocationRepository->getAllStandardLocationsOrderedByName();

    $toReturn = [];
    foreach ($standardLocations as $standardLocation) {
      $standardLocation->view_standard_location = [
        'href' => 'api/v1/avent/administration/standardLocation/' . $standardLocation->id,
        'method' => 'GET', ];

      $toReturn[] = $standardLocation;
    }

    return response()->json([
      'msg' => 'List of all standard locations',
      'standardLocations' => $toReturn, ]);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(int $id) {
    $standardLocation = $this->eventStandardLocationRepository->getStandardLocationById($id);

    if ($standardLocation == null) {
      return response()->json([
        'msg' => 'Standard location not found',
        'error_code' => 'standard_location_not_found', ], 404);
    }

    $standardLocation->view_standard_locations = [
      'href' => 'api/v1/avent/administration/standardLocation',
      'method' => 'GET', ];

    return response()->json([
      'msg' => 'Standard location information',
      'standardLocation' => $standardLocation, ]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required|min:1|max:190',
      'x' => 'numeric|nullable',
      'y' => 'numeric|nullable',
      'location' => 'string|nullable|max:190', ]);

    $name = $request->input('name');
    $location = $request->input('location');
    $x = $request->input('x');
    $y = $request->input('y');

    $standardLocation = $this->eventStandardLocationRepository->createStandardLocation($name, $location, $x, $y);

    if ($standardLocation == null) {
      return response()->json(['msg' => 'An error occurred during standard decision saving...'], 500);
    }

    $standardLocation->view_standard_location = [
      'href' => 'api/v1/avent/administration/standardLocation/' . $standardLocation->id,
      'method' => 'GET', ];

    return response()->json([
      'msg' => 'Successful created standard location',
      'standardLocation' => $standardLocation, ], 201);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function delete(int $id) {
    /*
     * Success is an integer because laravel returns the count of deleted objects. If the count is 0 there wasn't a
     * standard location found with this id and therefore not deleted
    */
    $success = $this->eventStandardLocationRepository->deleteStandardLocation($id);

    if ($success == 0) {
      return response()->json(['msg' => 'Standard location not found', 'error_code' => 'standard_location_not_found'], 404);
    }

    return response()->json(['msg' => 'Standard location deleted'], 200);
  }
}
