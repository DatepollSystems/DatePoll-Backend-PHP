<?php

namespace App\Repositories\System\Job;

use Illuminate\Support\Collection;

interface IJobRepository {
  /**
   * @return Collection
   */
  public function getUndoneJobs();
}
