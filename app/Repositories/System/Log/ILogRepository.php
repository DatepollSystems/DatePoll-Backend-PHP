<?php

namespace App\Repositories\System\Log;

use App\Models\System\Log;
use Exception;
use Illuminate\Support\Collection;

interface ILogRepository {
  /**
   * @return Collection<Log>
   */
  public function getAllLogsOrderedByDate();

  /**
   * @param int $id
   * @return Log|null
   */
  public function getLogById(int $id);

  /**
   * @param Log $log
   * @return bool
   * @throws Exception
   */
  public function deleteLogByLog(Log $log);

  public function deleteAllLogs();

  /**
   * @param string $logType
   * @param string $message
   * @param int|null $userId
   * @return Log
   */
  public function createLog(string $logType, string $message, ?int $userId = null);
}
