<?php

namespace App\Repositories\System\Log;

use App\Models\System\Log;
use Exception;
use Illuminate\Support\Facades\DB;

class LogRepository implements ILogRepository {

  /**
   * @return Log[]
   */
  public function getAllLogsOrderedByDate(): array {
    return Log::orderBy('created_at', 'desc')
      ->get()->all();
  }

  /**
   * @param int $id
   * @return Log|null
   */
  public function getLogById(int $id): ?Log {
    return Log::find($id);
  }

  /**
   * @param Log $log
   * @return bool|null
   * @throws Exception
   */
  public function deleteLogByLog(Log $log): ?bool {
    return $log->delete();
  }

  public function deleteAllLogs() {
    DB::table('logs')
      ->truncate();
  }

  /**
   * @param string $logType
   * @param string $message
   * @param int|null $userId
   * @return Log
   */
  public function createLog(string $logType, string $message, ?int $userId = null): ?Log {
    $log = new Log([
      'type' => $logType,
      'message' => $message,
      'user_id' => $userId, ]);

    return $log->save() ? $log : null;
  }
}
