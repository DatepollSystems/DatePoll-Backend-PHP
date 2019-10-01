<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\Controllers\Controller;
use App\Logging;
use App\Models\System\Log;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LoggingController extends Controller
{

  /**
   * @param Request $request
   * @return JsonResponse
   */
  public function getAllLogs(Request $request) {
    $logs = array();

    foreach (Log::all() as $log) {
      $logs[] = $log->getReturnable();
    }

    Logging::info("getAllLogs", "User - " . $request->auth->id . " | Successful");
    return response()->json(['msg' => 'All logs', 'logs' => $logs], 200);
  }

  /**
   * @param Request $request
   * @param $id
   * @return JsonResponse
   */
  public function deleteLog(Request $request, $id) {
    $log = Log::find($id);
    if ($log == null) {
      return response()->json(['msg' => 'Log not found'], 404);
    }

    if (!$log->delete()) {
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
    DB::table('logs')->truncate();

    Logging::info("deleteAllLogs", "User - " . $request->auth->id . " | Successful");
    return response()->json(['msg' => 'All logs deleted'], 200);
  }
}
