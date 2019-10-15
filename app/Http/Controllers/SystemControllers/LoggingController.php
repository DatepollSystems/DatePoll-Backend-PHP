<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\Log\ILogRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LoggingController extends Controller
{

  protected $logRepository = null;

  public function __construct(ILogRepository $logRepository)
  {
    $this->logRepository = $logRepository;
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getAllLogs(Request $request) {
    $logs = array();

    foreach ($this->logRepository->getAllLogsOrderedByDate() as $log) {
      $logs[] = $log->getReturnable();
    }

    Logging::info("getAllLogs", "User - " . $request->auth->id . " | Successful");
    return response()->json(['msg' => 'All logs', 'logs' => $logs], 200);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   * @throws Exception
   */
  public function deleteLog(Request $request, $id) {
    $log = $this->logRepository->getLogById($id);
    if ($log == null) {
      return response()->json(['msg' => 'Log not found'], 404);
    }

    if (!$this->logRepository->deleteLogByLog($log)) {
      return response()->json(['msg' => 'Could not delete log'], 404);
    }

    Logging::info("deleteLog", "User - " . $request->auth->id . " | Log - " . $id . " | Successful");
    return response()->json(['msg' => 'Log deleted'], 200);
  }

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function deleteAllLogs(Request $request) {
    $this->logRepository->deleteAllLogs();

    Logging::info("deleteAllLogs", "User - " . $request->auth->id . " | Successful");
    return response()->json(['msg' => 'All logs deleted'], 200);
  }
}
