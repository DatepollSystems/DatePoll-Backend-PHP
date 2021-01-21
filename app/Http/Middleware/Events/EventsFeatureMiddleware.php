<?php

namespace App\Http\Middleware\Events;

use App\Repositories\System\Setting\ISettingRepository;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventsFeatureMiddleware {
  protected ISettingRepository $settingRepository;

  public function __construct(ISettingRepository $settingRepository) {
    $this->settingRepository = $settingRepository;
  }

  /**
   * @param Request $request
   * @param Closure $next
   * @return JsonResponse
   */
  public function handle(Request $request, Closure $next): JsonResponse {
    if (! $this->settingRepository->getEventsEnabled()) {
      return response()->json([
        'msg' => 'The events feature is disabled on this DatePoll server',
        'error_code' => 'feature_disabled_events', ], 503);
    }

    return $next($request);
  }
}
