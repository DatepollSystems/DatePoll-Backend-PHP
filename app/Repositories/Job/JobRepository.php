<?php

namespace App\Repositories\Job;

use Illuminate\Support\Facades\DB;

class JobRepository implements IJobRepository
{

  public function getUndoneJobs() {
    return DB::table('jobs')->get();
  }
}