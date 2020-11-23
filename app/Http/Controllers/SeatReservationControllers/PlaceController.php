<?php

namespace App\Http\Controllers\SeatReservationControllers;

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
  public function getAll() {
    $places = $this->placeRepository->getAllPlaces();

    return response()->json(['msg' => 'List of all places', 'places' => $places]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, [
      'name' => 'required|string',
      'x' => 'nullable|numeric',
      'y' => 'nullable|numeric',
    ]);

    $name = $request->input('name');
    $x = $request->input('x');
    $y = $request->input('y');

    $place = $this->placeRepository->createOrUpdatePlace($name, $x, $y);

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
  public function update(Request $request, int $id) {
    $this->validate($request, [
      'name' => 'required|string',
      'x' => 'nullable|numeric',
      'y' => 'nullable|numeric',
    ]);

    $name = $request->input('name');
    $x = $request->input('x');
    $y = $request->input('y');

    $place = $this->placeRepository->getPlaceById($id);
    if ($place == null) {
      return response()->json(['msg' => 'Place not found!'], 404);
    }

    $place = $this->placeRepository->createOrUpdatePlace($name, $x, $y);

    if ($place == null) {
      return response()->json(['msg' => 'Could not update place'], 500);
    }

    return response()->json(['msg' => 'Successful updated place', 'place' => $place], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(Request $request, int $id) {
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
