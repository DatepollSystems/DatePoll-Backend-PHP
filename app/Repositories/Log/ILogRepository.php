<?php

namespace App\Repositories\Log;

use App\Models\System\Log;
use Exception;
use Illuminate\Support\Collection;

interface ILogRepository
{
  /**
   * @return Collection<Log>
   */
  public function getAllLogsOrderedByDate();

  /**
   * @param int $id
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