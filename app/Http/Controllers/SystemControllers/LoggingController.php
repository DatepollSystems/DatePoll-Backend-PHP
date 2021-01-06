<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\System\Log\ILogRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class LoggingController extends Controller {

  public function __construct(protected ILogRepository $logRepository) {
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getAllLogs(AuthenticatedRequest $request): JsonResponse {
    Logging::info('getAllLogs', 'User - ' . $request->auth->id . ' | Successful');

    return response()->json([
      'msg' => 'All logs',
      'logs' => $this->logRepository->getAllLogsOrderedByDate(),], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function saveLog(AuthenticatedRequest $request): JsonResponse {
    $this->validate($request, [
      'type' => 'required|string|min:1|max:190|in:INFO,WARNING,ERROR',
      'message' => 'required|string|min:1|max:65534',]);

    $log = $this->logRepository->createLog($request->input('type'), $request->input('message'), $request->auth->id);

    return response()->json([
      'msg' => 'Log saved',
      'log' => $log,], 201);
  }

  /**
   * @param AuthenticatedRequest $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function deleteLog(AuthenticatedRequest $request, int $id): JsonResponse {
    $log = $this->logRepository->getLogById($id);
    if ($log == null) {
      return response()->json(['msg' => 'Log not found'], 404);
    }

    if (! $this->logRepository->deleteLogByLog($log)) {
      return response()->json(['msg' => 'Could not delete log'], 404);
    }

    Logging::info('deleteLog', 'User - ' . $request->auth->id . ' | Log - ' . $id . ' | Successful');

    return response()->json(['msg' => 'Log deleted'], 200);
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function deleteAllLogs(AuthenticatedRequest $request): JsonResponse {
    $this->logRepository->deleteAllLogs();

    Logging::info('deleteAllLogs', 'User - ' . $request->auth->id . ' | Successful');

    return response()->json(['msg' => 'All logs deleted'], 200);
  }
}
