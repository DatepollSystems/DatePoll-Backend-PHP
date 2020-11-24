<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\System\Log\ILogRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class LoggingController extends Controller {
  protected ILogRepository $logRepository;

  public function __construct(ILogRepository $logRepository) {
    $this->logRepository = $logRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getAllLogs(Request $request) {
    $logs = [];

    foreach ($this->logRepository->getAllLogsOrderedByDate() as $log) {
      $logs[] = $log->getReturnable();
    }

    Logging::info('getAllLogs', 'User - ' . $request->auth->id . ' | Successful');

    return response()->json([
      'msg' => 'All logs',
      'logs' => $logs, ], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   * @throws ValidationException
   */
  public function saveLog(Request $request) {
    $this->validate($request, [
      'type' => 'required|string|min:1|max:190|in:INFO,WARNING,ERROR',
      'message' => 'required|string|min:1|max:65534', ]);

    $log = $this->logRepository->createLog($request->input('type'), $request->input('message'), $request->auth->id);

    return response()->json([
      'msg' => 'Log saved',
      'log' => $log->getReturnable(), ], 201);
  }

  /**
   * @param Request $request
   * @param int $id
   * @return JsonResponse
   * @throws Exception
   */
  public function deleteLog(Request $request, int $id) {
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
   * @param Request $request
   * @return JsonResponse
   */
  public function deleteAllLogs(Request $request) {
    $this->logRepository->deleteAllLogs();

    Logging::info('deleteAllLogs', 'User - ' . $request->auth->id . ' | Successful');

    return response()->json(['msg' => 'All logs deleted'], 200);
  }
}
