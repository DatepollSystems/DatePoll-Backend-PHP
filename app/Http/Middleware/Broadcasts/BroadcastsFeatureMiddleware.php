<?php

namespace App\Http\Middleware\Broadcasts;

use App\Repositories\System\Setting\ISettingRepository;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Lumen\Http\Redirector;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class BroadcastsFeatureMiddleware {
  protected ISettingRepository $settingRepository;

  public function __construct(ISettingRepository $settingRepository) {
    $this->settingRepository = $settingRepository;
  }

  /**
   * @param Request $request
   * @param Closure $next
   * @return JsonResponse|Response|BinaryFileResponse|Redirector|RedirectResponse
   */
  public function handle(Request $request, Closure $next): JsonResponse|Response|BinaryFileResponse|Redirector|RedirectResponse {
    if (! $this->settingRepository->getBroadcastsEnabled()) {
      return response()->json([
        'msg' => 'The broadcast feature is disabled on this DatePoll server',
        'error_code' => 'feature_disabled_broadcasts', ], 503);
    }

    return $next($request);
  }
}
