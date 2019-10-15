<?php

namespace App\Repositories\Log;

use App\Models\System\Log;
use Exception;

interface ILogRepository
{
  /**
   * @return Log[]
   */
  public function getAllLogsOrderedByDate();

  /**
   * @param $id
   * @return Log|null
   */
  public function getLogById($id);

  /**
   * @param Log $log
   * @return bool
   * @throws Exception
   */
  public function deleteLogByLog(Log $log);

  public function deleteAllLogs();
}