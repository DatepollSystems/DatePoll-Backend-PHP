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
  public function getAll(): JsonResponse {
    $standardLocations = $this->eventStandardLocationRepository->getAllStandardLocationsOrderedByName();

    return response()->json([
      'msg' => 'List of all standard locations',
      'standardLocations' => $standardLocations, ]);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(int $id): JsonResponse {
    $standardLocation = $this->eventStandardLocationRepository->getStandardLocationById($id);

    if ($standardLocation == null) {
      return response()->json([
        'msg' => 'Standard location not found',
        'error_code' => 'standard_location_not_found', ], 404);
    }

    return response()->json([
      'msg' => 'Standard location information',
      'standardLocation' => $standardLocation, ]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request): JsonResponse {
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

    return response()->json([
      'msg' => 'Successful created standard location',
      'standardLocation' => $standardLocation, ], 201);
  }

  /**
   * @param int $id
   * @return JsonResponse
   */
  public function delete(int $id): JsonResponse {
    if (! $this->eventStandardLocationRepository->deleteStandardLocation($id)) {
      return response()->json(['msg' => 'Standard location not found', 'error_code' => 'standard_location_not_found'], 404);
    }

    return response()->json(['msg' => 'Standard location deleted'], 200);
  }
}
