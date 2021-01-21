<?php

namespace App\Repositories\System\Job;

use App\Jobs\Job;

interface IJobRepository {
  /**
   * @return Job[]
   */
  public function getUndoneJobs(): array;
}
