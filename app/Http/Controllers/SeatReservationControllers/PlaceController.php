<?php

namespace App\Http\Controllers\SeatReservationControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\SeatReservation\Place\IPlaceRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PlaceController extends Controller {
  protected IPlaceRepository $placeRepository;

  public function __construct(IPlaceRepository $placeRepository) {
    $this->placeRepository = $placeRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll(): JsonResponse {
    return response()->json(['msg' => 'List of all places', 'places' => $this->placeRepository->getAllPlaces()]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request): JsonResponse {
    $this->validate($request, [
      'name' => 'required|string',
      'location' => 'string|nullable|max:190',
      'x' => 'nullable|numeric',
      'y' => 'nullable|numeric',
    ]);

    $name = $request->input('name');
    $location = $request->input('location');
    $x = $request->input('x');
    $y = $request->input('y');

    $place = $this->placeRepository->createOrUpdatePlace($name, $location, $x, $y);

    if ($place == null) {
      return response()->json(['msg' => 'Could not create place'], 500);
    }

    return response()->json(['msg' => 'Successful created place', 'place' => $place], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, int $id): JsonResponse {
    $this->validate($request, [
      'name' => 'required|string',
      'location' => 'string|nullable|max:190',
      'x' => 'nullable|numeric',
      'y' => 'nullable|numeric',
    ]);

    $name = $request->input('name');
    $location = $request->input('location');
    $x = $request->input('x');
    $y = $request->input('y');

    $place = $this->placeRepository->getPlaceById($id);
    if ($place == null) {
      return response()->json(['msg' => 'Place not found!'], 404);
    }

    $place = $this->placeRepository->createOrUpdatePlace($name, $location, $x, $y);

    if ($place == null) {
      return response()->json(['msg' => 'Could not update place'], 500);
    }

    return response()->json(['msg' => 'Successful updated place', 'place' => $place], 201);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(AuthenticatedRequest $request, int $id): JsonResponse {
    $place = $this->placeRepository->getPlaceById($id);
    if ($place == null) {
      return response()->json(['msg' => 'Place not found'], 404);
    }

    if (! $this->placeRepository->deletePlace($place)) {
      Logging::error('deletePlace', 'Could not delete place! User id - ' . $request->auth->id);

      return response()->json(['msg' => 'Could not delete place'], 500);
    }

    Logging::info('deletePlace', 'Deleted place! User id - ' . $request->auth->id);

    return response()->json(['msg' => 'Successfully deleted place'], 200);
  }
}
