<?php

namespace App\Repositories\System\Job;

use Illuminate\Support\Facades\DB;

class JobRepository implements IJobRepository {
  public function getUndoneJobs() {
    return DB::table('jobs')->orderBy('failed_at', 'DESC')->get();
  }
}
