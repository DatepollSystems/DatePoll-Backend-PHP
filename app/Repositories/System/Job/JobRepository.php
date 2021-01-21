<?php

namespace App\Repositories\System\Job;

use App\Jobs\Job;
use Illuminate\Support\Facades\DB;

class JobRepository implements IJobRepository {
  /**
   * @return Job[]
   */
  public function getUndoneJobs(): array {
    return DB::table('jobs')->orderBy('failed_at', 'DESC')->get()->all();
  }
}
