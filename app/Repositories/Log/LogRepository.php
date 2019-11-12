<?php

namespace App\Repositories\Log;

use App\Models\System\Log;
use Illuminate\Support\Facades\DB;

class LogRepository implements ILogRepository
{

  public function getAllLogsOrderedByDate() {
    return Log::orderBy('created_at', 'desc')
              ->get();
  }

  public function getLogById($id) {
    return Log::find($id);
  }

  public function deleteLogByLog(Log $log) {
    return $log->delete();
  }

  public function deleteAllLogs() {
    DB::table('logs')
      ->truncate();
  }

  public function createLog(string $logType, string $message) {
    $log = new Log([
      'type' => $logType,
      'message' => $message]);

    return $log->save();
  }
}