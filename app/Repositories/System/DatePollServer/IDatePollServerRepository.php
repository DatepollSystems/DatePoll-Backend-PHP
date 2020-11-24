<?php

namespace App\Repositories\System\DatePollServer;

use stdClass;

interface IDatePollServerRepository {
  /**
   * @return stdClass
   */
  public function getServerInfo();
}
