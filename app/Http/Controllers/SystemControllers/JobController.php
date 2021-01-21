<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Logging;
use App\Repositories\System\Job\IJobRepository;
use Illuminate\Http\JsonResponse;

class JobController extends Controller {
  protected IJobRepository $jobRepository;

  public function __construct(IJobRepository $jobRepository) {
    $this->jobRepository = $jobRepository;
  }

  /**
   * @param AuthenticatedRequest $request
   * @return JsonResponse
   */
  public function getUndoneJobs(AuthenticatedRequest $request): JsonResponse {
    $jobs = $this->jobRepository->getUndoneJobs();

    Logging::info('getFailedJobs', 'User - ' . $request->auth->id . ' | Successful');

    return response()->json(['msg' => 'All jobs', 'jobs' => $jobs], 200);
  }
}
