<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\Controllers\Controller;
use App\Repositories\System\DatePollServer\IDatePollServerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DatePollServerController extends Controller
{

  protected IDatePollServerRepository $datePollServerRepository;

  public function __construct(IDatePollServerRepository $datePollServerRepository) {
    $this->datePollServerRepository = $datePollServerRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getServerInfo() {
    $cacheKey = 'server.info';
    if (Cache::has($cacheKey)) {
      return response()->json(Cache::get($cacheKey), 200);
    }

    $serverInfo = $this->datePollServerRepository->getServerInfo();

    // Time to live 60 minutes
    Cache::put($cacheKey, $serverInfo, 60*60);

    return response()->json($serverInfo, 200);
  }
}
