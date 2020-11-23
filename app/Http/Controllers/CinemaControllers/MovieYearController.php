<?php

namespace App\Http\Controllers\CinemaControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\Cinema\MovieYear\IMovieYearRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class MovieYearController extends Controller {
  protected IMovieYearRepository $movieYearRepository;

  public function __construct(IMovieYearRepository $movieYearRepository) {
    $this->movieYearRepository = $movieYearRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getAll() {
    $years = $this->movieYearRepository->getMovieYearsOrderedByDate();
    foreach ($years as $year) {
      $year->view_year = [
        'href' => 'api/v1/cinema/administration/year/' . $year->id,
        'method' => 'GET', ];
    }

    return response()->json([
      'msg' => 'List of all years',
      'years' => $years, ]);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function create(Request $request) {
    $this->validate($request, ['year' => 'required|integer']);

    $movieYear = $this->movieYearRepository->createMovieYear($request->input('year'));

    if ($movieYear != null) {
      $movieYear->view_year = [
        'href' => 'api/v1/cinema/administration/year/' . $movieYear->id,
        'method' => 'GET', ];

      Logging::info('createMovieYear', 'User - ' . $request->auth->id . ' | Created movie year - ' . $movieYear->id);

      return response()->json([
        'msg' => 'Year created',
        'year' => $movieYear, ], 201);
    }

    Logging::error('createMovieYear', 'User - ' . $request->auth->id . ' | Year - ' . $request->input('year') . ' | An error occurred during movie year saving');

    return response()->json(['msg' => 'An error occurred during movie year saving'], 500);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   */
  public function getSingle(Request $request, int $id) {
    $year = $this->movieYearRepository->getMovieYearById($id);
    if ($year == null) {
      Logging::warning('getSingleMovieYear', 'User - ' . $request->auth->id . ' | Movie year id - ' . $id . ' | Movie year not found');

      return response()->json(['msg' => 'Movie year not found'], 404);
    }

    $year->view_years = [
      'href' => 'api/v1/cinema/administration/year',
      'method' => 'GET', ];

    return response()->json([
      'msg' => 'Year information',
      'year' => $year, ]);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws ValidationException
   */
  public function update(Request $request, int $id) {
    $this->validate($request, ['year' => 'required|integer']);

    $movieYear = $this->movieYearRepository->getMovieYearById($id);
    if ($movieYear == null) {
      Logging::warning('updateMovieYear', 'User - ' . $request->auth->id . ' | Movie year id - ' . $id . ' | Movie year not found');

      return response()->json(['msg' => 'Movie year not found'], 404);
    }

    $movieYear = $this->movieYearRepository->updateMovieYear($movieYear, $request->input('year'));

    if ($movieYear != null) {
      $movieYear->view_year = [
        'href' => 'api/v1/cinema/administration/year/' . $movieYear->id,
        'method' => 'GET', ];

      return response()->json([
        'msg' => 'Year updated',
        'year' => $movieYear, ], 201);
    }

    Logging::error('updateMovieYear', 'User - ' . $request->auth->id . ' | Movie year id - ' . $id . ' | An error occurred during movie year updating');

    return response()->json(['msg' => 'An error occurred during movie year updating'], 500);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function delete(Request $request, int $id) {
    $movieYear = $this->movieYearRepository->getMovieYearById($id);
    if ($movieYear == null) {
      Logging::warning('updateMovieYear', 'User - ' . $request->auth->id . ' | Movie year id - ' . $id . ' | Movie year not found');

      return response()->json(['msg' => 'Movie year not found'], 404);
    }

    if (! $this->movieYearRepository->deleteMovieYear($movieYear)) {
      Logging::error('deleteMovieYear', 'User - ' . $request->auth->id . ' | Movie year id - ' . $id . ' | An error occurred during movie year deletion');

      return response()->json(['msg' => 'Movie year deletion failed'], 404);
    }

    return response()->json([
      'msg' => 'Year deleted',
      'create' => [
        'href' => 'api/v1/cinema/administration/year',
        'method' => 'POST',
        'params' => 'year', ], ]);
  }
}
