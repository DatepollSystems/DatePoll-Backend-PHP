<?php

namespace App\Http\Controllers\SystemControllers;

use App\Http\Controllers\Controller;
use App\Repositories\System\DatePollServer\IDatePollServerRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class DatePollServerController extends Controller {
  private static string $SERVER_INFO_CACHE_KEY = 'server.info';

  protected IDatePollServerRepository $datePollServerRepository;

  public function __construct(IDatePollServerRepository $datePollServerRepository) {
    $this->datePollServerRepository = $datePollServerRepository;
  }

  /**
   * @return JsonResponse
   */
  public function getServerInfo(): JsonResponse {
    if (Cache::has(self::$SERVER_INFO_CACHE_KEY)) {
      return response()->json(Cache::get(self::$SERVER_INFO_CACHE_KEY), 200);
    }

    $serverInfo = $this->datePollServerRepository->getServerInfo();

    // Time to live 60 minutes
    Cache::put(self::$SERVER_INFO_CACHE_KEY, $serverInfo, 60 * 60);

    return response()->json($serverInfo, 200);
  }
}
