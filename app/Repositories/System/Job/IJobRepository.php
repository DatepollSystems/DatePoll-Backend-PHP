<?php

namespace App\Repositories\System\Job;

use App\Models\System\Log;
use Exception;
use Illuminate\Support\Collection;

interface IJobRepository
{
  /**
   * @return Collection
   */
  public function getUndoneJobs();
}