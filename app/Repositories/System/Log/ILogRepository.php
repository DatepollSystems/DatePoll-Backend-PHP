<?php

namespace App\Repositories\System\Log;

use App\Models\System\Log;
use Exception;

interface ILogRepository {
  /**
   * @return Log[]
   */
  public function getAllLogsOrderedByDate(): array;

  /**
   * @param int $id
   * @return Log|null
   */
  public function getLogById(int $id): ?Log;

  /**
   * @param Log $log
   * @return bool
   * @throws Exception
   */
  public function deleteLogByLog(Log $log): bool;

  public function deleteAllLogs();

  /**
   * @param string $logType
   * @param string $message
   * @param int|null $userId
   * @return Log|null
   */
  public function createLog(string $logType, string $message, ?int $userId = null): ?Log;
}
